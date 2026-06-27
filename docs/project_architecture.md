# Arsitektur Proyek: EventSync Pro

Dokumen ini merinci arsitektur perangkat lunak untuk **EventSync Pro**, yang membagi sistem menjadi lapisan Backend (Laravel 12+ Service-Repository) dan Frontend (Vue 3 Feature-Based Components) dalam satu proyek Monolith modern terintegrasi lewat Inertia.js.

---

## 1. Peta Struktur Direktori (Folder Structure)

Struktur proyek monolith modern ini memadukan backend PHP Laravel dan frontend Vue 3 secara modular:

```text
EventSync Pro/
├── app/
│   ├── Contracts/                   # Kontrak/Interface Abstraksi (BE)
│   │   ├── Repositories/            # Abstraksi Data Access (misal: TaskRepositoryInterface)
│   │   └── Services/                # Abstraksi Bisnis Logika (misal: TaskServiceInterface)
│   ├── Repositories/
│   │   └── Eloquent/                # Implementasi konkret Data Access dengan Eloquent ORM
│   │       ├── TaskRepository.php
│   │       └── BaseRepository.php
│   ├── Services/                    # Implementasi konkret Alur Kerja Bisnis
│   │   └── TaskService.php
│   ├── Http/
│   │   └── Controllers/             # Controller tipis, bertugas meneruskan data ke Inertia
│   │       └── TaskController.php
│   ├── Events/                      # Kelas Event untuk Reverb Broadcast
│   │   └── TaskMoved.php
│   └── Providers/
│       └── AppServiceProvider.php   # Melakukan binding Interface ke Concrete Class
├── config/
├── database/
├── resources/
│   ├── js/
│   │   ├── app.js                   # Inisialisasi Inertia + Vue
│   │   ├── Layouts/                 # Tata letak global (Sidebar, Navbar)
│   │   ├── Components/
│   │   │   └── ui/                  # Komponen global dari shadcn-vue (Button, Modal, Input)
│   │   ├── Features/                # Feature-Based Components (FE)
│   │   │   ├── Tasks/               # Struktur modular khusus fitur Tasks
│   │   │   │   ├── Components/      # Sub-komponen (KanbanBoard, TaskCard, TaskDetailModal)
│   │   │   │   ├── Composables/     # Logic hook Vue (useKanban, useReverb)
│   │   │   │   └── Stores/          # Pinia/Ref state management lokal task
│   │   │   ├── Projects/
│   │   │   └── Workspaces/
│   │   └── Pages/                   # Halaman utama yang dirender oleh Inertia (Dashboard.vue)
│   └── css/
│       └── app.css                  # Konfigurasi Tailwind CSS
└── routes/
    ├── web.php                      # Rute web stateful (Inertia Render)
    └── channels.php                 # Rute otorisasi WebSocket Laravel Reverb
```

---

## 2. Arsitektur Backend (Service-Repository Pattern)

Pola Service-Repository berbasis interface menjamin fleksibilitas dan kemudahan pengujian unit (*mocking repository*).

### 2.1. Abstraksi Lapisan Repository (Contracts)
```php
<?php
// app/Contracts/Repositories/TaskRepositoryInterface.php

namespace App\Contracts\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function findById(string $id): ?Task;
    public function getTasksByTeam(string $teamId): Collection;
    public function getTasksByProject(string $projectId): Collection;
    public function create(array $data): Task;
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
}
```

### 2.2. Implementasi Lapisan Repository (Eloquent)
```php
<?php
// app/Repositories/Eloquent/TaskRepository.php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Contracts\Repositories\TaskRepositoryInterface;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function findById(string $id): ?Task
    {
        return Task::with(['comments', 'attachments', 'assignee'])->find($id);
    }

    public function getTasksByTeam(string $teamId): Collection
    {
        return Task::where('team_id', $teamId)->orderBy('created_at', 'desc')->get();
    }

    public function getTasksByProject(string $projectId): Collection
    {
        return Task::where('project_id', $projectId)->orderBy('created_at', 'desc')->get();
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $task = Task::find($id);
        return $task ? $task->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $task = Task::find($id);
        return $task ? $task->delete() : false;
    }
}
```

### 2.3. Lapisan Service (Business Logic)
Service memproses aturan otorisasi bisnis, pemanggilan repositori, serta menembakkan Event Real-time.
```php
<?php
// app/Contracts/Services/TaskServiceInterface.php

namespace App\Contracts\Services;

use App\Models\Task;
use App\Models\User;

interface TaskServiceInterface
{
    public function getBoardData(string $projectId, User $user): array;
    public function createNewTask(array $data, User $creator): Task;
    public function moveTaskStatus(string $taskId, string $newStatusId, User $updater): bool;
}
```

```php
<?php
// app/Services/TaskService.php

namespace App\Services;

use App\Contracts\Services\TaskServiceInterface;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Events\TaskMoved;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Exception;

class TaskService implements TaskServiceInterface
{
    protected $taskRepo;

    public function __construct(TaskRepositoryInterface $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function getBoardData(string $projectId, User $user): array
    {
        // 1. Cek apakah user adalah Project Leader / Co-Leader
        $isLeader = $user->projectMembers()
            ->where('project_id', $projectId)
            ->whereIn('role_project', ['leader', 'co_leader'])
            ->exists();

        if ($isLeader) {
            // Project Leader melihat seluruh task
            return $this->taskRepo->getTasksByProject($projectId)->toArray();
        }

        // 2. User biasa: Hanya melihat task dari tim tempat dia bergabung di project tersebut
        $userTeamIds = $user->teams()
            ->where('project_id', $projectId)
            ->pluck('teams.id');

        $tasks = collect();
        foreach ($userTeamIds as $teamId) {
            $tasks = $tasks->merge($this->taskRepo->getTasksByTeam($teamId));
        }

        return $tasks->toArray();
    }

    public function createNewTask(array $data, User $creator): Task
    {
        $data['created_by'] = $creator->id;
        return $this->taskRepo->create($data);
    }

    public function moveTaskStatus(string $taskId, string $newStatusId, User $updater): bool
    {
        $task = $this->taskRepo->findById($taskId);
        if (!$task) {
            throw new Exception("Tugas tidak ditemukan");
        }

        // Cek otorisasi: Harus anggota tim task tersebut ATAU project leader
        $isAuthorized = $updater->projectMembers()
            ->where('project_id', $task->project_id)
            ->whereIn('role_project', ['leader', 'co_leader'])
            ->exists() || $updater->teamMembers()->where('team_id', $task->team_id)->exists();

        if (!$isAuthorized) {
            throw new Exception("Anda tidak memiliki izin memindahkan tugas ini");
        }

        $updated = $this->taskRepo->update($taskId, ['status_id' => $newStatusId]);

        if ($updated) {
            // Siarkan event real-time ke Laravel Reverb
            broadcast(new TaskMoved($task->project_id, $taskId, $newStatusId))->toOthers();
        }

        return $updated;
    }
}
```

### 2.4. Melakukan Binding di AppServiceProvider
Untuk mendaftarkan dependency injection kontainer:
```php
<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Repositories\Eloquent\TaskRepository;
use App\Contracts\Services\TaskServiceInterface;
use App\Services\TaskService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        
        // Bind Service
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
    }
}
```

---

## 3. Arsitektur Frontend (Feature-Based Vue 3)

Pengorganisasian folder Vue dikelompokkan berdasarkan fungsionalitas fitur bisnis untuk mencegah kekacauan file.

### 3.1. Struktur Fitur `Tasks` (`resources/js/Features/Tasks`)
Setiap fitur mandiri memiliki komponen visual, store penampung data local, dan composable:
- **`Stores/useTaskStore.js`**:
  ```javascript
  import { defineStore } from 'pinia';
  import { ref } from 'vue';

  export const useTaskStore = defineStore('tasks', () => {
    const tasks = ref([]);
    
    function setTasks(newTasks) {
      tasks.value = newTasks;
    }
    
    function updateTaskStatus(taskId, newStatusId) {
      const task = tasks.value.find(t => t.id === taskId);
      if (task) {
        task.status_id = newStatusId;
      }
    }

    return { tasks, setTasks, updateTaskStatus };
  });
  ```

- **`Composables/useReverb.js`** (Integrasi Laravel Echo dengan Reverb):
  ```javascript
  import { onMounted, onUnmounted } from 'vue';
  import { useTaskStore } from '../Stores/useTaskStore';

  export function useReverb(projectId) {
    const taskStore = useTaskStore();

    onMounted(() => {
      window.Echo.private(`project.${projectId}`)
        .listen('TaskMoved', (e) => {
          // Memperbarui state secara real-time dari WebSocket broadcast
          taskStore.updateTaskStatus(e.taskId, e.newStatusId);
        });
    });

    onUnmounted(() => {
      window.Echo.leave(`project.${projectId}`);
    });
  }
  ```

### 3.2. Integrasi UI shadcn-vue & Animasi Motion JS
Untuk interaktivitas premium drag-and-drop kartu Kanban yang mulus menggunakan Motion JS:
```vue
<!-- resources/js/Features/Tasks/Components/TaskCard.vue -->
<script setup>
import { onMounted, ref } from 'vue';
import { animate } from 'motion';
import { Card, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';

const props = defineProps({
  task: Object
});

const cardRef = ref(null);

onMounted(() => {
  // Efek micro-animation pendaratan saat kartu pertama kali dimuat di Kanban
  animate(
    cardRef.value,
    { opacity: [0, 1], scale: [0.95, 1], y: [15, 0] },
    { duration: 0.35, easing: "ease-out" }
  );
});
</script>

<template>
  <div ref="cardRef" class="cursor-grab active:cursor-grabbing my-2">
    <Card class="hover:border-primary/50 transition-colors shadow-sm">
      <CardHeader class="p-4">
        <div class="flex items-center justify-between mb-1">
          <span :class="[
            'text-xs font-semibold px-2 py-0.5 rounded',
            task.priority === 'high' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'
          ]">
            {{ task.priority }}
          </span>
        </div>
        <CardTitle class="text-sm font-bold text-slate-800">{{ task.title }}</CardTitle>
        <CardDescription class="text-xs text-slate-500 line-clamp-2 mt-1">
          {{ task.description }}
        </CardDescription>
      </CardHeader>
    </Card>
  </div>
</template>
```

---

## 4. Pola Real-time Event (Laravel Reverb)

Konfigurasi saluran komunikasi real-time terenkripsi untuk kolaborasi tim.

### 4.1. Definisi Channel (`routes/channels.php`)
```php
<?php
// routes/channels.php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('project.{projectId}', function (User $user, string $projectId) {
    // Memastikan user terdaftar di project_members atau salah satu team di project tersebut
    $hasProjectAccess = $user->projectMembers()->where('project_id', $projectId)->exists()
        || $user->teams()->where('project_id', $projectId)->exists();
        
    return $hasProjectAccess;
});
```

### 4.2. Definisi Event Broadcast (`app/Events/TaskMoved.php`)
```php
<?php
// app/Events/TaskMoved.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projectId;
    public $taskId;
    public $newStatusId;

    public function __construct(string $projectId, string $taskId, string $newStatusId)
    {
        $this->projectId = $projectId;
        $this->taskId = $taskId;
        $this->newStatusId = $newStatusId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('project.' . $this->projectId),
        ];
    }
}
```
