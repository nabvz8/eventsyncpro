<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo" />
</p>

<h1 align="center">EventSync Pro</h1>
<p align="center"><em>Platform Manajemen Proyek & Tim Berbasis Kanban Board</em></p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js" />
  <img src="https://img.shields.io/badge/PostgreSQL-16-336791?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL" />
  <img src="https://img.shields.io/badge/Inertia.js-Monolith-9553E9?style=for-the-badge" alt="Inertia" />
</p>

---

## 📌 Tentang Proyek

**EventSync Pro** adalah aplikasi manajemen proyek dan kolaborasi tim berbasis web yang dirancang untuk membantu organisasi atau tim mengelola alur kerja pengerjaan tugas secara visual menggunakan **Papan Kanban**. Proyek ini dikembangkan sebagai tugas Mata Kuliah **Basis Data**.

Aplikasi ini memungkinkan sebuah *workspace* memiliki banyak proyek, setiap proyek memiliki beberapa tim pelaksana, dan setiap tim memiliki akses data tugas yang terisolasi sesuai keanggotaannya — kecuali bagi Project Leader/Co-Leader yang dapat melihat seluruh data proyek.

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|---|---|
| 🔐 **Autentikasi** | Register, Login, Logout dengan validasi lengkap |
| 🚀 **Onboarding Wizard** | Alur setup profil & workspace untuk pengguna baru |
| 🏢 **Multi-Workspace** | Satu akun dapat bergabung atau memiliki beberapa workspace |
| 📁 **Manajemen Proyek** | Buat proyek dalam workspace dengan role Leader & Co-Leader |
| 👥 **Manajemen Tim** | Setiap proyek memiliki beberapa tim dengan Leader, Co-Leader, dan Member |
| 📋 **Kanban Board** | Papan tugas visual dengan kolom status dinamis (To Do, In Progress, Done) |
| 🖱️ **Drag & Drop** | Pindahkan kartu tugas antar kolom secara langsung dengan HTML5 DnD API |
| 🔒 **Otorisasi Berlapis** | Anggota tim hanya melihat tugas timnya; Leader proyek melihat semua |
| 🎨 **Animasi Modern** | Transisi dan animasi dengan Motion JS (stagger, fade, scale) |

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12+ (PHP 8.2+)
- **Arsitektur**: Monolith dengan Service-Repository Pattern
- **Database**: PostgreSQL 16
- **Real-time**: Laravel Reverb *(direncanakan)*
- **ORM**: Eloquent dengan UUID sebagai primary key

### Frontend
- **Framework**: Vue.js 3 (Composition API + `<script setup>`)
- **Bridge**: Inertia.js (SPA tanpa API terpisah)
- **State Management**: Pinia
- **UI Components**: Shadcn-Vue + Reka UI
- **Animasi**: Motion JS
- **Styling**: Tailwind CSS v3

### Tools & Testing
- **Build**: Vite
- **Testing**: PHPUnit (Feature Tests)
- **Version Control**: Git

---

## 🏗️ Arsitektur

```
EventSync Pro (Monolit)
│
├── Backend (Laravel 12+)
│   ├── app/Contracts/       — Interface Repository & Service
│   ├── app/Repositories/    — Implementasi Eloquent Repository
│   ├── app/Services/        — Business Logic Layer
│   ├── app/Http/Controllers/— HTTP Request Handler
│   └── app/Models/          — Eloquent Models (UUID)
│
└── Frontend (Vue 3 + Inertia)
    └── resources/js/
        ├── Features/        — Feature-based Components
        │   ├── Tasks/       — Kanban Board, Task Card, Create Modal
        │   ├── Teams/       — Team List Card
        │   └── Projects/    — Create Project Modal
        ├── Pages/           — Halaman Inertia (SSR props)
        ├── Layouts/         — Layout Utama & Guest
        └── Components/      — Komponen Shared
```

---

## 🗃️ Skema Database

```
users
 └── workspaces (owner_id)
      └── workspace_members (pivot: role_workspace)
      └── projects (workspace_id)
           └── project_members (pivot: role_project)
           └── task_statuses (project_id, position)
           └── teams (project_id)
                └── team_members (pivot: role_team)
                └── tasks (team_id, status_id, assigned_to)
```

---

## ⚙️ Cara Instalasi & Menjalankan

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js >= 18
- PostgreSQL >= 14

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/nabvz8/eventsyncpro.git
cd eventsyncpro

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node
npm install

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

### Konfigurasi Database (`.env`)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=eventsync_pro
DB_USERNAME=laravel
DB_PASSWORD=123
```

### Migrasi & Seed

```bash
# Jalankan migrasi dan seed data demo
php artisan migrate:fresh --seed
```

### Menjalankan Aplikasi

```bash
# Terminal 1 — Backend
php artisan serve

# Terminal 2 — Frontend (dev mode)
npm run dev
```

Buka di browser: **http://localhost:8000**

### Akun Demo (setelah seeding)

| Role | Email | Password |
|---|---|---|
| Owner | owner@eventsync.com | password |
| Member | member@eventsync.com | password |

---

## 🧪 Testing

```bash
# Jalankan seluruh test suite
php artisan test

# Jalankan test tertentu
php artisan test --filter TaskTest
php artisan test --filter TeamTest
php artisan test --filter ProjectTest
```

> ✅ **44 tests, 111 assertions** — 100% Passing

---

## 📂 Dokumentasi Tambahan

Dokumentasi teknis tersedia di folder [`/docs`](./docs):

- [`system_analysis.md`](./docs/system_analysis.md) — Analisis sistem & kebutuhan
- [`project_architecture.md`](./docs/project_architecture.md) — Desain arsitektur
- [`database_design.md`](./docs/database_design.md) — ERD dan desain database
- [`database_schema.sql`](./docs/database_schema.sql) — Schema SQL lengkap
- [`routes_and_controllers.md`](./docs/routes_and_controllers.md) — Daftar rute & controller
- [`sprint_plan.md`](./docs/sprint_plan.md) — Rencana sprint pengembangan

---

## 👤 Pengembang

Dikembangkan sebagai tugas **Mata Kuliah Basis Data**

---

## 📄 Lisensi

Proyek ini merupakan karya akademik. Seluruh kode ditulis untuk keperluan pembelajaran.
