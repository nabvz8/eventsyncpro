# Rencana Pengerjaan Sprint (Sprint Plan): EventSync Pro

Rencana kerja ini menggunakan siklus **Sprint 1 Mingguan (6 Sprints)** untuk membangun sistem informasi manajemen tugas **EventSync Pro** dari tahap awal hingga MVP selesai sepenuhnya.

Setiap Sprint memiliki fokus granular pada Backend (BE) dan Frontend (FE) serta deliverable yang dapat diuji.

---

## 📅 Ringkasan Timeline Sprint
```mermaid
gantt
    title Timeline Pengembangan EventSync Pro (6 Minggu)
    dateFormat  YYYY-MM-DD
    section Backend & Database
    Sprint 1 : Setup, Migrations & Auth        :active, 2026-06-28, 7d
    Sprint 2 : Onboarding & Workspaces         : 2026-07-05, 7d
    Sprint 3 : Projects & Project Roles        : 2026-07-12, 7d
    Sprint 4 : Teams & Team Member Roles       : 2026-07-19, 7d
    Sprint 5 : Kanban Board & Statuses         : 2026-07-26, 7d
    Sprint 6 : Reverb Real-time & Comments     : 2026-08-02, 7d
```

---

## 🏃 Sprint 1: Setup Proyek, Basis Data, & Autentikasi
**Fokus utama**: Menginisialisasi codebase monolith, menyiapkan relasi database PostgreSQL, dan mengimplementasikan sistem pendaftaran & masuk akun.

### 🛠️ Daftar Backlog Pengerjaan
#### A. Backend (BE) - Laravel 12+
- [ ] Setup instalasi proyek Laravel 12 baru dan instalasi PostgreSQL driver.
- [ ] Membuat file migrasi database PostgreSQL (Tabel `users`, `workspaces`, `workspace_members`).
- [ ] Menulis Eloquent Model: `User.php`, `Workspace.php`, `WorkspaceMember.php` beserta relasinya.
- [ ] Membuat Interface & Class Repository:
  - `UserRepositoryInterface.php` & `UserRepository.php`
- [ ] Membuat Interface & Class Service:
  - `AuthServiceInterface.php` & `AuthService.php` (Logika login, registrasi, hashing kata sandi).
- [ ] Registrasi binding di `AppServiceProvider.php`.
- [ ] Membuat `AuthController.php` (menangani HTTP request dan return Inertia render).
- [ ] Menambahkan web routes (`/register`, `/login`, `/logout`) di `routes/web.php`.

#### B. Frontend (FE) - Vue 3 + Inertia
- [ ] Setup inisialisasi Inertia.js dan Vue 3 di proyek Laravel.
- [ ] Instalasi **shadcn-vue** (Tailwind CSS, Radix Vue, lucide-vue-next).
- [ ] Membuat halaman **Register Page** (`resources/js/Pages/Auth/Register.vue`) menggunakan UI input, button, dan label shadcn.
- [ ] Membuat halaman **Login Page** (`resources/js/Pages/Auth/Login.vue`) dengan validasi form client-side.
- [ ] Implementasi sistem flash notification/toast untuk umpan balik error login.

### 📦 Deliverable Akhir Sprint
- Pengguna dapat mengakses halaman `/register` dan `/login`.
- Registrasi menyimpan data `users` ke database dengan password terhashing.
- Login berhasil mengalihkan pengguna ke sesi login aktif dengan session-cookie.

---

## 🏃 Sprint 2: Onboarding Wizard & Manajemen Workspace
**Fokus utama**: Mengarahkan pengguna baru untuk melengkapi profil dan membuat/bergabung ke ruang kerja pertama mereka.

### 🛠️ Daftar Backlog Pengerjaan
#### A. Backend (BE) - Laravel 12+
- [ ] Membuat middleware `EnsureUserIsOnboarded` untuk memfilter pengguna yang belum menyelesaikan profil agar tidak bisa mengakses dashboard.
- [ ] Membuat Interface & Class Repository:
  - `WorkspaceRepositoryInterface.php` & `WorkspaceRepository.php`
- [ ] Membuat Interface & Class Service:
  - `OnboardingServiceInterface.php` & `OnboardingService.php` (Logic: simpan profil user, create workspace, generate template project default).
- [ ] Membuat `OnboardingController.php` dan `WorkspaceController.php`.
- [ ] Registrasi binding repository & service baru di `AppServiceProvider.php`.
- [ ] Menambahkan rute `/onboarding` (GET/POST) dan `/workspaces` (POST/PUT/DELETE).

#### B. Frontend (FE) - Vue 3 + Inertia
- [ ] Membuat halaman **Onboarding Page** (`resources/js/Pages/Onboarding/Index.vue`):
  - Step 1: Lengkapi profil (Nama Lengkap, Pekerjaan/Role).
  - Step 2: Form Input Pembuatan Workspace Pertama.
  - Step 3: Pilihan template project default (misal: "Kanban Default").
- [ ] Membuat komponen global **Sidebar Layout** (`resources/js/Layouts/AuthenticatedLayout.vue`) yang memuat dropdown switcher antar-Workspace yang dimiliki pengguna.

### 📦 Deliverable Akhir Sprint
- Pengguna yang baru login otomatis diarahkan ke `/onboarding`.
- Menyelesaikan onboarding berhasil meng-update profil user, membuat baris baru di tabel `workspaces` & `workspace_members` (peran 'owner'), dan membuat proyek perdana.
- Sidebar menampilkan switcher workspace yang berfungsi.

---

## 🏃 Sprint 3: Manajemen Proyek & Peran Proyek (Project Access)
**Fokus utama**: Membuat kontainer proyek di dalam Workspace dan menunjuk struktur kepemimpinan proyek.

### 🛠️ Daftar Backlog Pengerjaan
#### A. Backend (BE) - Laravel 12+
- [ ] Membuat migrasi database tabel `projects` dan `project_members`.
- [ ] Menulis Eloquent Model: `Project.php` dan `ProjectMember.php` dengan relasi `belongsToMany` ke `User`.
- [ ] Membuat Interface & Class Repository:
  - `ProjectRepositoryInterface.php` & `ProjectRepository.php`
- [ ] Membuat Interface & Class Service:
  - `ProjectServiceInterface.php` & `ProjectService.php` (Logic: validasi kepemilikan workspace, simpan project, daftarkan project members dengan role 'leader' dan 'co_leader').
- [ ] Membuat `ProjectController.php` (method `store` dan `show`).
- [ ] Menambahkan rute `/projects/{id}` dan `/workspaces/{wsId}/projects` (POST).

#### B. Frontend (FE) - Vue 3 + Inertia
- [ ] Membuat halaman **Project Show Page** (`resources/js/Pages/Projects/Show.vue`) sebagai cangkang workspace Kanban.
- [ ] Membuat modal dialog **Create Project Form** (`resources/js/Features/Projects/Components/CreateProjectModal.vue`) menggunakan dialog shadcn.
- [ ] Membuat tab **Project Members Settings** untuk menampilkan list member berhak akses project dan menunjuk Co-Leader.

### 📦 Deliverable Akhir Sprint
- Admin Workspace dapat membuat Project baru.
- Halaman detail proyek `/projects/{id}` dapat diakses oleh user yang diotorisasi.
- Pemilik proyek dapat menambahkan member proyek dan mengubah perannya menjadi `leader` atau `co_leader` di database `project_members`.

---

## 🏃 Sprint 4: Manajemen Tim (Teams) di bawah Proyek
**Fokus utama**: Membuat tim-tim pelaksana di bawah Project dan mengatur hak akses khusus per-tim.

### 🛠️ Daftar Backlog Pengerjaan
#### A. Backend (BE) - Laravel 12+
- [ ] Membuat migrasi database tabel `teams` (merujuk ke `project_id`) dan `team_members` (memuat kolom `role_team` enum).
- [ ] Menulis Eloquent Model: `Team.php` dan `TeamMember.php`.
- [ ] Membuat Interface & Class Repository:
  - `TeamRepositoryInterface.php` & `TeamRepository.php`
- [ ] Membuat Interface & Class Service:
  - `TeamServiceInterface.php` & `TeamService.php` (Logic: validasi bahwa pembuat tim adalah Project Leader/Co-Leader, simpan tim, daftarkan anggota dengan role 'leader', 'co_leader', atau 'member').
- [ ] Membuat `TeamController.php` (method `store` dan `addMember`).
- [ ] Menambahkan rute `/projects/{projId}/teams` (POST) dan `/teams/{teamId}/members` (POST).

#### B. Frontend (FE) - Vue 3 + Inertia
- [ ] Membuat tab **Team Management Panel** di dalam halaman Project detail.
- [ ] Membuat komponen Vue **Team List Card** (`resources/js/Features/Teams/Components/TeamListCard.vue`) untuk menampilkan daftar tim beserta anggota dan perannya di tim tersebut (Team Leader, Co-Leader, Member).
- [ ] Membuat form undang/tambahkan anggota workspace ke dalam tim proyek.

### 📦 Deliverable Akhir Sprint
- Project Leader/Co-Leader dapat membentuk beberapa Team (misal: "Frontend Team") di bawah suatu Project.
- Pengguna dapat dimasukkan sebagai anggota tim dengan peran yang dispesifikasi.
- Struktur tim dan anggota tercatat di database dengan integritas foreign key yang benar.

---

## 🏃 Sprint 5: Papan Kanban, Status Dinamis, & Pengerjaan Tugas (Tasks)
**Fokus utama**: Membangun papan visual Kanban dengan kolom status dinamis per-proyek, membuat tugas, dan menerapkan animasi mikro Motion JS.

### 🛠️ Daftar Backlog Pengerjaan
#### A. Backend (BE) - Laravel 12+
- [ ] Membuat migrasi database tabel `task_statuses` dan tabel `tasks` (memuat kolom `project_id`, `team_id`, dan `status_id`).
- [ ] Menulis Eloquent Model: `TaskStatus.php` dan `Task.php` dengan relasi logisnya.
- [ ] Membuat Interface & Class Repository:
  - `TaskRepositoryInterface.php` & `TaskRepository.php`
- [ ] Membuat Interface & Class Service:
  - `TaskServiceInterface.php` & `TaskService.php` (Logic: memfilter task di method `getBoardData` sesuai batasan tim pengguna jika bukan Project Leader, update status task).
- [ ] Membuat `TaskController.php` (method `store` dan `update`).
- [ ] Menambahkan rute `/projects/{projId}/tasks` (POST) dan `/tasks/{id}` (PUT - untuk handle perpindahan status).

#### B. Frontend (FE) - Vue 3 + Inertia
- [ ] Membuat folder struktur fitur tugas: `resources/js/Features/Tasks/`.
- [ ] Membuat Pinia store `useTaskStore.js` untuk mengelola state lokal array tasks di browser.
- [ ] Membuat komponen **Kanban Board** (`resources/js/Features/Tasks/Components/KanbanBoard.vue`) yang memetakan kolom dari data `statuses` props.
- [ ] Membuat komponen **Task Card** (`resources/js/Features/Tasks/Components/TaskCard.vue`) menggunakan card UI dari shadcn.
- [ ] Menerapkan library **Motion JS** pada component lifecycle:
  - Animasi pendaratan kartu (*fade-in & slide-up*) saat Kanban board pertama kali dimuat.
  - Animasi transisi pergeseran koordinat kartu saat di-drag antar kolom.
- [ ] Membuat form **Create Task Dialog** (memilih judul, assignee, priority, due date, dan mengaitkan ke salah satu tim pelaksana).

### 📦 Deliverable Akhir Sprint
- Halaman `/projects/{id}` menampilkan papan Kanban.
- User hanya dapat melihat kartu tugas (Task) yang sesuai dengan timnya (kecuali Project Leader/Co-Leader yang melihat semua kartu).
- Menyeret (drag-and-drop) kartu tugas memicu request update data di backend secara instan tanpa me-refresh halaman web, didukung animasi transisi yang mulus.

---

## 🏃 Sprint 6: Fitur Kolaborasi Tambahan & Real-time Sync (Laravel Reverb)
**Fokus utama**: Melengkapi fungsionalitas detail tugas (komentar, subtask, lampiran file) dan menyinkronkan data Kanban board secara real-time via WebSocket.

### 🛠️ Daftar Backlog Pengerjaan
#### A. Backend (BE) - Laravel 12+
- [ ] Membuat migrasi database tabel `task_comments` dan `task_attachments`.
- [ ] Setup instalasi dan konfigurasi **Laravel Reverb** sebagai server WebSocket.
- [ ] Membuat kelas Event Real-time `TaskMoved.php` yang mengimplementasikan `ShouldBroadcast` ke Private Channel `project.{projectId}`.
- [ ] Mengonfigurasi otorisasi broadcast channel di `routes/channels.php` untuk memverifikasi hak akses user ke proyek.
- [ ] Menulis logika penyimpanan komentar dan unggahan lampiran berkas (maksimal 5MB) di `CommentController` dan `AttachmentController`.
- [ ] Memodifikasi `TaskService` agar memicu broadcast event `TaskMoved` saat status tugas diubah.

#### B. Frontend (FE) - Vue 3 + Inertia
- [ ] Membuat modal panel detail tugas **Task Detail Modal** yang menampilkan:
  - Daftar centang subtask bersarang (parent-child task relasional).
  - Kolom diskusi komentar task dengan timestamp.
  - File uploader untuk lampiran berkas tugas.
- [ ] Menulis composable hook Vue `useReverb.js` untuk menginisialisasi listener Laravel Echo:
  - Mendengarkan event `TaskMoved` di private channel project.
  - Memperbarui Pinia store `tasks` secara otomatis saat menerima sinyal websocket agar tampilan board pengguna lain ter-update secara instan.
- [ ] Implementasi micro-animation (pop-up/pulse) menggunakan Motion JS pada kartu tugas ketika statusnya diperbarui secara real-time oleh anggota tim lain.

### 📦 Deliverable Akhir Sprint
- Pengguna dapat membuat subtask, berdiskusi melalui kolom komentar, dan mengunggah dokumen pendukung di dalam detail tugas.
- Ketika Anggota Tim A memindahkan tugas di papan Kanbannya, tampilan papan Kanban Anggota Tim B di browser berbeda akan ikut bergeser secara otomatis dan instan secara real-time tanpa memuat ulang halaman.
