# Rute & Controller Monolith: EventSync Pro

Dokumen ini merinci rancangan rute web (`routes/web.php`) dan antarmuka Controller yang mengirimkan data ke halaman Vue 3 menggunakan **Inertia.js**. 

Di arsitektur monolith Inertia, alur data tidak melalui endpoint REST JSON statis. Sebaliknya, Controller mengambil data melalui **Service**, memfilternya sesuai otorisasi, lalu merender halaman Vue dengan mengirimkan data sebagai **Inertia Props**. Setelah operasi POST/PUT/DELETE, Controller biasanya mengalihkan kembali (*redirect back*), memaksa Inertia memuat ulang data terbaru secara otomatis.

---

## 1. Alur Rute Autentikasi & Onboarding

| Rute Web | Method | Controller & Action | Kegunaan | Inertia Page / Redirect |
| :--- | :--- | :--- | :--- | :--- |
| `/register` | GET | `AuthController@showRegister` | Form registrasi user baru | `Auth/Register` |
| `/register` | POST | `AuthController@register` | Menyimpan data user baru | Redirect ke `/onboarding` |
| `/login` | GET | `AuthController@showLogin` | Form login user | `Auth/Login` |
| `/login` | POST | `AuthController@login` | Memproses sesi login | Redirect ke `/dashboard` atau `/onboarding` |
| `/onboarding` | GET | `OnboardingController@index` | Form wizard onboarding | `Onboarding/Index` |
| `/onboarding` | POST | `OnboardingController@submit`| Menyimpan data onboarding pertama | Redirect ke `/project/{id}` |

### Detail Data Props Onboarding (`GET /onboarding`)
```json
{
  "templates": [
    { "id": "kanban_default", "name": "Standard Kanban" },
    { "id": "agile_sprint", "name": "Agile Sprint Board" }
  ],
  "roles_selection": [
    "Product Manager", "Developer", "Designer", "QA Engineer", "Other"
  ]
}
```

---

## 2. Alur Rute Workspace Management

| Rute Web | Method | Controller & Action | Kegunaan | Inertia Page / Redirect |
| :--- | :--- | :--- | :--- | :--- |
| `/dashboard` | GET | `DashboardController@index` | Dashboard utama | `Dashboard` |
| `/workspaces` | POST | `WorkspaceController@store` | Membuat Workspace baru | Redirect ke `/dashboard` |
| `/workspaces/{id}`| PUT | `WorkspaceController@update`| Mengubah deskripsi/nama workspace | Redirect back |
| `/workspaces/{id}`| DELETE | `WorkspaceController@destroy`| Menghapus workspace permanen | Redirect ke `/dashboard` |

### Detail Data Props Dashboard (`GET /dashboard`)
```json
{
  "auth": {
    "user": {
      "id": "u1111-2222...",
      "full_name": "Budi Setiawan",
      "global_role": "user"
    }
  },
  "workspaces": [
    {
      "id": "w9999-8888...",
      "name": "Budi's Engineering Hub",
      "role": "owner"
    }
  ],
  "invitations": [] // Daftar undangan aktif yang belum diterima
}
```

---

## 3. Alur Rute Project & Team Management

| Rute Web | Method | Controller & Action | Kegunaan | Inertia Page / Redirect |
| :--- | :--- | :--- | :--- | :--- |
| `/projects/{id}` | GET | `ProjectController@show` | Menampilkan workspace proyek & Kanban Board | `Projects/Show` |
| `/workspaces/{wsId}/projects` | POST | `ProjectController@store` | Membuat Project baru | Redirect ke `/projects/{newProjectId}` |
| `/projects/{projId}/teams` | POST | `TeamController@store` | Membuat Team baru di dalam Project | Redirect back |
| `/teams/{teamId}/members` | POST | `TeamController@addMember`| Menambahkan anggota baru ke dalam Team | Redirect back |

### Detail Data Props Project Board (`GET /projects/{id}`)
Ini adalah data utama untuk memuat Kanban Board. Data Task disaring otomatis di backend berdasarkan peran user:
```json
{
  "project": {
    "id": "p1p2p3-...",
    "name": "Redesign Landing Page",
    "user_role": "member", // project role: 'leader' | 'co_leader' | 'member'
    "teams": [
      {
        "id": "t1t2t3-...",
        "name": "Team Frontend",
        "members": [
          { "id": "u1111...", "full_name": "Rian", "role_team": "leader" }
        ]
      }
    ]
  },
  "statuses": [
    { "id": "s111...", "name": "To Do", "color_hex": "#F44336", "position": 1 },
    { "id": "s222...", "name": "In Progress", "color_hex": "#2196F3", "position": 2 }
  ],
  "tasks": [
    // Jika requester adalah Project Leader/Co-Leader, seluruh task tampil.
    // Jika requester adalah anggota Team Frontend, hanya task ber-team_id Frontend yang tampil.
    {
      "id": "tsk999...",
      "team_id": "t1t2t3-...", // ID Tim pelaksana
      "status_id": "s111...",
      "title": "Wireframe Homepage",
      "priority": "high",
      "due_date": "2026-07-05",
      "assignee": { "id": "u1111...", "full_name": "Rian" },
      "subtasks": []
    }
  ]
}
```

---

## 4. Alur Rute Task Management

Operasi Task dilakukan dengan memicu request POST/PUT via Inertia Form (`router.post`, `router.put`) yang kemudian melakukan reload props secara halus (*Partial Reloads*) agar tidak me-refresh seluruh halaman.

| Rute Web | Method | Controller & Action | Kegunaan | Inertia Page / Redirect |
| :--- | :--- | :--- | :--- | :--- |
| `/projects/{projId}/tasks` | POST | `TaskController@store` | Membuat Task baru di bawah Project | Redirect back |
| `/tasks/{id}` | PUT | `TaskController@update` | Memperbarui status / detail / assignee Task | Redirect back |
| `/tasks/{id}` | DELETE | `TaskController@destroy`| Menghapus Task | Redirect back |
| `/tasks/{id}/comments` | POST | `CommentController@store` | Menambahkan komentar baru | Redirect back |
| `/tasks/{id}/attachments`| POST | `AttachmentController@store`| Mengunggah lampiran file | Redirect back |

### Payload Request Update Task (`PUT /tasks/{id}`)
Saat user melakukan drag-and-drop kartu Kanban dari status "To Do" ke "In Progress":
```javascript
// Di sisi Vue Frontend:
import { router } from '@inertiajs/vue3'

function onDragEnd(taskId, newStatusId) {
  router.put(`/tasks/${taskId}`, {
    status_id: newStatusId
  }, {
    preserveScroll: true, // Mencegah halaman scroll ke atas saat state ter-reload
  });
}
```
*Response Backend (Controller):*
```php
public function update(Request $request, string $id)
{
    $updater = $request->user();
    
    // Validasi data
    $validated = $request->validate([
        'status_id' => 'required|uuid|exists:task_statuses,id',
        'priority' => 'nullable|string|in:low,medium,high',
        'title' => 'nullable|string|max:255'
    ]);

    // Memproses via Service
    $this->taskService->moveTaskStatus($id, $validated['status_id'], $updater);

    // Mengalihkan kembali (Inertia otomatis me-refresh database props secara dinamis)
    return redirect()->back()->with('message', 'Task updated successfully!');
}
```
