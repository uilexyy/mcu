# MCU — RS Juwita Medical Check Up Management System

Laravel 12 backend API + Vue 3 SPA frontend. Mengelola alur MCU dari pendaftaran karyawan hingga hasil akhir.

---

## Commands

| Action | Command |
|--------|---------|
| Full setup | `composer setup` |
| Dev servers | `composer dev` (serves app + queue + logs + Vite concurrently) |
| Run tests | `composer test` (runs `artisan config:clear`, then `artisan test`) |
| Run a single test | `php artisan test tests/Path/To/Test.php` |
| PHP lint | `./vendor/bin/pint` |
| Backend only | `php artisan serve` |
| Frontend dev | `cd frontend && npm run dev` (port 5173) |
| Frontend build | `cd frontend && npm run build` (outputs to `frontend/dist/`) |

## Dev server behavior

`composer dev` runs 4 processes via `concurrently`:
- `php artisan serve` (backend API at `localhost:8000`)
- `php artisan queue:listen --tries=1 --timeout=0`
- `php artisan pail --timeout=0` (real-time log viewer)
- `npm run dev` (runs from root, serves `resources/js/` — not used; actual frontend is in `frontend/`)

> **Catatan:** Frontend SPA ada di `frontend/` dan dijalankan terpisah via `cd frontend && npm run dev`. Vite dev server di `frontend/` proxy `/api` dan `/storage` ke `localhost:8000`.

Kill processes together (Ctrl+C on the `concurrently` parent).

---

## Database

- **Dev**: MySQL (`DB_DATABASE=mcu` in `.env`)
- **Testing**: SQLite `:memory:` (configured in `phpunit.xml`)
- Session, cache, and queue use the `database` driver by default
- Run new migrations: `php artisan migrate`

### Migrations (14 file)

| File | Table |
|------|-------|
| `0001_01_01_000000_create_users_table` | `users` |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` |
| `2024_01_01_000001_create_mcu_packages` | `mcu_packages` |
| `2024_01_01_000002_create_mcu_package_items` | `mcu_package_items` |
| `2024_01_01_000003_create_mcu_registrations` | `mcu_registrations` |
| `2024_01_01_000004_create_mcu_physical_exams` | `mcu_physical_exams` |
| `2024_01_01_000005_create_mcu_lab_results` | `mcu_lab_results` |
| `2024_01_01_000006_create_mcu_radiology_results` | `mcu_radiology_results` |
| `2024_01_01_000007_create_mcu_results` | `mcu_results` |
| `2026_05_21_022110_create_personal_access_tokens` | `personal_access_tokens` |
| `2026_05_21_042817_add_signature_to_users` | Add `signature` ke `users` |
| `2026_05_21_044500_add_gelar_to_users` | Add `gelar_depan`, `gelar_belakang` ke `users` |
| `2026_05_21_073854_create_activity_logs` | `activity_logs` |

---

## Code style

- Laravel Pint (`./vendor/bin/pint`) — PSR-12 aligned
- EditorConfig: 4-space indent, LF endings
- Frontend: Tailwind CSS v3, PostCSS, autoprefixer

---

## Testing

- `composer test` always runs `artisan config:clear` first
- Unit tests extend `PHPUnit\Framework\TestCase` (no Laravel app boot)
- Feature tests extend `Tests\TestCase` (SQLite in-memory)
- `RefreshDatabase` trait is **not** used by default — add per test class if needed

---

## Architecture — Backend (Laravel 12 API)

### API Base

Semua endpoint di-prefix `/api/v1`. Auth via **Laravel Sanctum** (token-based).

### Roles & Route Middleware

| Role | Middleware `role:` | Deskripsi |
|------|-------------------|-----------|
| `admin` | `admin` | Kelola pendaftaran, paket, user, statistik, export Excel |
| `karyawan` | `karyawan` | Daftar MCU, lihat histori, download hasil |
| `dokter_umum` | `dokter_umum` | Pemeriksaan fisik |
| `laboratorium` | `laboratorium` | Input hasil lab |
| `radiologi` | `radiologi` | Input hasil radiologi + generate PDF final |

### API Routes (`routes/api.php`)

```
POST   /api/v1/auth/register           — Registrasi (role: karyawan)
POST   /api/v1/auth/login              — Login
POST   /api/v1/auth/logout             — Logout

GET    /api/v1/profile                 — Lihat profil
PUT    /api/v1/profile                 — Update profil
POST   /api/v1/profile/signature       — Upload tanda tangan
PUT    /api/v1/profile/password        — Ganti password

GET    /api/v1/packages                — Lihat paket MCU (all roles)

# Karyawan
GET    /api/v1/karyawan/registrations         — Daftar pendaftaran sendiri
POST   /api/v1/karyawan/registrations         — Daftar MCU baru
GET    /api/v1/karyawan/registrations/{id}/download — Download PDF hasil

# Admin
GET    /api/v1/admin/registrations            — Semua pendaftaran (filterable)
GET    /api/v1/admin/export/registrations     — Export Excel
PUT    /api/v1/admin/registrations/{id}/approve — Setujui
PUT    /api/v1/admin/registrations/{id}/reject   — Tolak
GET    /api/v1/admin/stats                    — Statistik dashboard
GET    /api/v1/admin/activity-logs            — Log aktivitas
GET    /api/v1/admin/packages                 — CRUD paket MCU (apiResource)
POST       .../packages
PUT        .../packages/{id}
DELETE     .../packages/{id}
GET    /api/v1/admin/users                    — CRUD user (apiResource)

# Dokter Umum
GET    /api/v1/dokter/queue                   — Antrian periksa hari ini
POST   /api/v1/dokter/physical-exam           — Input pemeriksaan fisik
PUT    /api/v1/dokter/physical-exam/{id}      — Update pemeriksaan
GET    /api/v1/dokter/riwayat                 — Riwayat pemeriksaan sendiri
GET    /api/v1/dokter/registrations/{id}/history — Detail registrasi

# Laboratorium
GET    /api/v1/lab/queue                      — Antrian lab
POST   /api/v1/lab/results                    — Input hasil lab
PUT    /api/v1/lab/results/{id}               — Update hasil lab
GET    /api/v1/lab/riwayat                    — Riwayat sendiri
GET    /api/v1/lab/registrations/{id}/history — Detail registrasi

# Radiologi
GET    /api/v1/radiologi/queue                — Antrian radiologi
POST   /api/v1/radiologi/results              — Input hasil + upload foto
PUT    /api/v1/radiologi/results/{id}         — Update hasil
GET    /api/v1/radiologi/riwayat              — Riwayat sendiri
GET    /api/v1/radiologi/registrations/{id}/history — Detail registrasi
```

### Controllers (`app/Http/Controllers/`)

| Path | Method |
|------|--------|
| `Controller.php` | Base abstract class |
| `Api/Auth/AuthController.php` | `register`, `login`, `logout` |
| `Api/Auth/ProfileController.php` | `show`, `update`, `uploadSignature`, `updatePassword` |
| `Api/Admin/RegistrationController.php` | `index`, `show`, `approve`, `reject`, `export`, `stats`, `logs` |
| `Api/Admin/UserController.php` | `index`, `store`, `show`, `update`, `destroy` |
| `Api/Admin/PackageController.php` | `index`, `store`, `show`, `update`, `destroy` |
| `Api/Karyawan/RegistrationController.php` | `index`, `store`, `download` |
| `Api/Dokter/PhysicalExamController.php` | `queue`, `store`, `update`, `riwayat`, `history` |
| `Api/Lab/LabResultController.php` | `queue`, `store`, `update`, `riwayat`, `history` |
| `Api/Radiologi/RadiologiController.php` | `queue`, `store`, `update`, `riwayat`, `history` |

### Models (`app/Models/`)

| Model | Table | Key Fillable | Trait |
|-------|-------|-------------|-------|
| `User` | `users` | `name`, `gelar_depan`, `gelar_belakang`, `email`, `password`, `nip`, `departemen`, `tanggal_lahir`, `jenis_kelamin`, `role`, `signature` | `HasApiTokens`, `HasFactory`, `Notifiable`, `LogActivity` |
| `McuPackage` | `mcu_packages` | `nama_paket`, `deskripsi`, `harga`, `is_active` | `HasFactory`, `LogActivity` |
| `McuPackageItem` | `mcu_package_items` | `package_id`, `nama_pemeriksaan`, `satuan`, `nilai_normal` | `HasFactory` |
| `McuRegistration` | `mcu_registrations` | `user_id`, `package_id`, `status`, `tanggal_jadwal`, `catatan_admin`, `foto_ktp` | `HasFactory`, `LogActivity` |
| `McuPhysicalExam` | `mcu_physical_exams` | `registration_id`, `doctor_id`, `tekanan_darah`, `berat_badan`, `tinggi_badan`, `imt`, `anamnesis`, `catatan` | `HasFactory`, `LogActivity` |
| `McuLabResult` | `mcu_lab_results` | `registration_id`, `lab_user_id`, `item_id`, `nilai`, `keterangan` | `HasFactory` |
| `McuRadiologyResult` | `mcu_radiology_results` | `registration_id`, `radio_user_id`, `interpretasi`, `file_path` | `HasFactory` |
| `McuResult` | `mcu_results` | `registration_id`, `pdf_path`, `generated_at` | `HasFactory` |
| `ActivityLog` | `activity_logs` | `user_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `description` | — |

### Status Flow Registrasi

```
pending → approved → doctor_done → lab_done → completed
                          ↘ radiology_done ↗
pending → rejected
```

### Key Packages (composer.json)

| Package | Purpose |
|---------|---------|
| `laravel/framework ^12.0` | Laravel 12 |
| `laravel/sanctum ^4.3` | API token auth |
| `barryvdh/laravel-dompdf ^3.1` | Generate PDF hasil MCU |
| `maatwebsite/excel ^3.1` | Export Excel rekap pendaftaran |
| `laravel/pail ^1.2.2` | Real-time log viewer (dev) |
| `laravel/pint ^1.24` | PHP linter (dev) |

### Trait — `LogActivity`

Digunakan oleh model: `User`, `McuPackage`, `McuRegistration`, `McuPhysicalExam`. Otomatis log `created`/`updated`/`deleted` ke tabel `activity_logs` dalam Bahasa Indonesia.

### Export — `RegistrationsExport`

Export Excel dengan fitur: filter status/search/tanggal, styling (header biru, warna selang-seling, auto-filter, freeze pane), judul sheet "Rekap MCU", status dalam Bahasa Indonesia.

### Views — Blade (`resources/views/`)

| View | Description |
|------|-------------|
| `welcome.blade.php` | Halaman welcome Laravel default |
| `pdf/mcu-result.blade.php` | Template PDF hasil MCU (151 baris) — header RS, data karyawan, hasil fisik/lab/radiologi, tanda tangan dokter |

---

## Architecture — Frontend (Vue 3 SPA)

### Stack

- **Vue 3** (Composition API)
- **Pinia** (state management)
- **Vue Router** (role-based route guards)
- **Tailwind CSS v3** (dark mode via `class`)
- **Chart.js + vue-chartjs** (dashboard charts)
- **Axios** (HTTP client with auth interceptor)

### Struktur (`frontend/`)

```
frontend/
├── index.html                  # Entry HTML (font Inter, title "RS Juwita - Medical Check Up")
├── vite.config.js              # Proxy /api & /storage ke localhost:8000
├── tailwind.config.js          # Dark mode class, custom font Inter, shimmer animation
├── postcss.config.js
├── package.json                # Axios, Pinia, Vue 3, Vue Router, Chart.js, Tailwind
└── src/
    ├── main.js                 # Bootstrap (createApp + Pinia + Router)
    ├── App.vue                 # Root: <router-view> + <ToastContainer>
    ├── assets/main.css         # Tailwind directives + dark mode overrides
    ├── router/index.js         # Routes + role-based navigation guards
    ├── stores/
    │   ├── auth.js             # Auth store (login, register, logout, user)
    │   └── toast.js            # Toast notification store
    ├── utils/
    │   ├── axios.js            # Axios instance (baseURL, token interceptor)
    │   └── errors.js           # Validation error parser
    ├── composables/
    │   └── usePolling.js       # Polling utility (setInterval/clearInterval)
    ├── layouts/
    │   ├── AppLayout.vue       # Sidebar + topbar + dark mode toggle
    │   └── AuthLayout.vue      # Minimal layout untuk login/register
    ├── components/
    │   ├── BadgeStatus.vue     # Status badge (warna berdasarkan status)
    │   ├── BaseButton.vue, BaseCard.vue, BaseInput.vue, BaseSelect.vue
    │   ├── EmptyState.vue, LoadingSpinner.vue
    │   ├── PageHeader.vue
    │   ├── SkeletonCard.vue, SkeletonTable.vue
    │   └── ToastContainer.vue  # Notifikasi toast
    └── pages/
        ├── auth/LoginPage.vue, RegisterPage.vue
        ├── karyawan/DashboardPage.vue, RegisterMcuPage.vue, HistoryPage.vue
        ├── admin/DashboardPage.vue, RegistrationsPage.vue, RegistrationDetailPage.vue,
        │        PackagesPage.vue, UsersPage.vue, ActivityLogsPage.vue
        ├── dokter/QueuePage.vue, PhysicalExamPage.vue, RiwayatPage.vue
        ├── lab/QueuePage.vue, LabResultsPage.vue, RiwayatPage.vue
        └── radiologi/QueuePage.vue, RadiologiPage.vue, RiwayatPage.vue
```

### Halaman per Role

| Role | Halaman |
|------|---------|
| **karyawan** | Dashboard, Daftar MCU, Riwayat |
| **admin** | Dashboard (statistik + grafik), Kelola Pendaftaran, Detail Registrasi, Paket MCU, Users, Activity Logs |
| **dokter_umum** | Antrian, Pemeriksaan Fisik, Riwayat |
| **laboratorium** | Antrian, Input Hasil Lab, Riwayat |
| **radiologi** | Antrian, Input Hasil + Upload Foto, Riwayat |

### Frontend Dev Server

Jalankan terpisah:
```
cd frontend && npm run dev
```
- Port: 5173
- Proxy `/api` → `http://localhost:8000`
- Proxy `/storage` → `http://localhost:8000`
- Build output: `frontend/dist/` (untuk production, di-serve via SPA catch-all di `routes/web.php`)

---

## Catatan Penting

- Frontend SPA terpisah dari Laravel (bukan di `resources/js/`)
- Semua interaksi frontend-backend via API JSON (Sanctum token)
- PDF final MCU digenerate otomatis oleh RadiologiController setelah input hasil + foto
- Activity log otomatis tercatat untuk model yang menggunakan trait `LogActivity`
- Dark mode didukung via Tailwind `class` strategy + toggle di AppLayout
