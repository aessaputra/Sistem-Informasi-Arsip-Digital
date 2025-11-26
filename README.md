# Sistem Informasi Arsip Digital

<p align="center">
  <strong>Digital Mail Archive Information System</strong><br>
  Sistem Manajemen Arsip Surat Masuk dan Surat Keluar
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.31-FF2D20?style=flat&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Tabler-UI-0054A6?style=flat" alt="Tabler">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

## 📋 Tentang Aplikasi

Sistem Informasi Arsip Digital adalah aplikasi web berbasis Laravel yang dirancang untuk mengelola arsip surat masuk dan surat keluar secara digital. Aplikasi ini membantu organisasi atau instansi dalam mencatat, menyimpan, dan mengelola dokumen surat dengan lebih terstruktur dan efisien.

### ✨ Fitur Utama

- **📥 Manajemen Surat Masuk**
  - CRUD (Create, Read, Update, Delete) surat masuk
  - Upload file lampiran dokumen
  - Pencarian dan filter data
  - Klasifikasi surat berdasarkan kategori

- **📤 Manajemen Surat Keluar**
  - CRUD surat keluar
  - Upload file lampiran dokumen
  - Pencarian dan filter data
  - Klasifikasi surat berdasarkan kategori

- **🏷️ Klasifikasi Surat**
  - Pengelolaan kategori/klasifikasi surat
  - Status aktif/non-aktif untuk klasifikasi

- **👥 Manajemen Pengguna**
  - Role-based access control (RBAC)
  - Role Admin: Akses penuh ke seluruh sistem
  - Role Operator: Akses terbatas untuk manajemen surat

- **📊 Dashboard**
  - Statistik surat masuk dan keluar
  - Aktivitas terbaru
  - Ringkasan data harian

- **📝 Log Aktivitas**
  - Pencatatan semua aktivitas pengguna
  - Audit trail untuk keamanan data

- **👤 Profil Pengguna**
  - Update informasi profil
  - Upload foto profil/avatar
  - Ubah password

- **🎨 UI Modern**
  - Menggunakan Tabler UI Framework
  - Dark/Light mode toggle
  - Responsive design untuk semua perangkat

## 🛠️ Teknologi yang Digunakan

### Backend
- **Laravel 11.31** - PHP Framework
- **PHP 8.2+** - Programming Language
- **Spatie Laravel Permission** - Role & Permission Management

### Frontend
- **Tabler** - Admin Dashboard UI Kit
- **Alpine.js** - Lightweight JavaScript Framework
- **TailwindCSS** - Utility-first CSS Framework
- **Vite** - Build Tool & Development Server

### Database
- **SQLite** (Default) - Lightweight Database
- Support untuk **MySQL** / **PostgreSQL**

### Authentication
- **Laravel Breeze** - Simple Authentication Scaffolding

## 📦 Persyaratan Sistem

Pastikan sistem Anda memenuhi persyaratan berikut:

- **PHP** >= 8.2
- **Composer** - PHP Dependency Manager
- **Node.js** >= 18.x
- **npm** - Node Package Manager
- **Database**: SQLite / MySQL / PostgreSQL
- **Web Server**: Apache / Nginx / Laravel Serve

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/aessaputra/Sistem-Informasi-Arsip-Digital.git
cd Sistem-Informasi-Arsip-Digital
```

### 2. Install Dependencies PHP

```bash
composer install
```

### 3. Install Dependencies Node.js

```bash
npm install
```

### 4. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

**Untuk Windows PowerShell:**
```powershell
copy .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Konfigurasi Database

Edit file `.env` sesuai dengan database yang Anda gunakan:

**Untuk SQLite (Default - Recommended untuk Development):**
```env
DB_CONNECTION=sqlite
# Hapus atau comment baris DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

**Untuk MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arsip_digital
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Buat Database SQLite (jika menggunakan SQLite)

```bash
# Linux/Mac
touch database/database.sqlite

# Windows PowerShell
New-Item database/database.sqlite -ItemType File
```

### 8. Migrate Database & Seed Data

```bash
php artisan migrate --seed
```

Perintah ini akan:
- Membuat semua tabel database
- Membuat role (Admin & Operator)
- Membuat user default

### 9. Create Storage Link

```bash
php artisan storage:link
```

### 10. Build Assets

**Untuk Development:**
```bash
npm run dev
```

**Untuk Production:**
```bash
npm run build
```

## 🎯 Menjalankan Aplikasi

### Development Server

Jalankan perintah berikut di terminal yang berbeda:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Vite Dev Server (jika development):**
```bash
npm run dev
```

Atau gunakan composer script untuk menjalankan semua service sekaligus:
```bash
composer dev
```

Aplikasi akan tersedia di: **http://localhost:8000**

### Default Login Credentials

Setelah seeding database, Anda dapat login dengan akun berikut:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@admin.com | password |
| **Operator** | operator@operator.com | password |

> ⚠️ **PENTING**: Segera ubah password default setelah login pertama kali!

## 📖 Panduan Penggunaan

### Role & Permissions

#### 👨‍💼 Admin
- Mengelola semua surat masuk dan keluar
- Mengelola pengguna (CRUD)
- Mengelola klasifikasi surat
- Melihat log aktivitas sistem
- Akses penuh ke dashboard

#### 👨‍💻 Operator
- Mengelola surat masuk (CRUD)
- Mengelola surat keluar (CRUD)
- Akses terbatas ke dashboard
- Tidak dapat mengelola pengguna dan klasifikasi

### Workflow Surat Masuk

1. Login sebagai Admin atau Operator
2. Navigasi ke menu **Surat Masuk**
3. Klik tombol **Tambah Surat Masuk**
4. Isi form dengan data:
   - Tanggal Surat
   - Nomor Surat
   - Perihal
   - Dari (Pengirim)
   - Kepada (Penerima)
   - Tanggal Surat Masuk
   - Klasifikasi Surat
   - Keterangan (opsional)
   - Upload File (opsional)
5. Klik **Simpan**

### Workflow Surat Keluar

1. Login sebagai Admin atau Operator
2. Navigasi ke menu **Surat Keluar**
3. Klik tombol **Tambah Surat Keluar**
4. Isi form dengan data yang relevan
5. Klik **Simpan**

### Mengelola Klasifikasi Surat (Admin Only)

1. Login sebagai Admin
2. Navigasi ke menu **Klasifikasi**
3. Tambah, edit, atau nonaktifkan klasifikasi
4. Setiap klasifikasi memiliki:
   - Kode unik
   - Nama klasifikasi
   - Keterangan
   - Status (Aktif/Tidak Aktif)

## 📁 Struktur Proyek

```
Sistem-Informasi-Arsip-Digital/
├── app/
│   ├── Http/
│   │   └── Controllers/      # Controllers untuk routing
│   ├── Models/               # Eloquent Models
│   └── View/                 # View Components
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Public assets
├── resources/
│   ├── views/                # Blade templates
│   ├── css/                  # CSS files
│   └── js/                   # JavaScript files
├── routes/
│   ├── web.php               # Web routes
│   └── auth.php              # Authentication routes
├── storage/
│   └── app/                  # File uploads
├── tabler/                   # Tabler UI assets
└── .env                      # Environment configuration
```

## ⚙️ Konfigurasi

### Upload File

Konfigurasi ukuran maksimum file upload dapat diatur di file `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Timezone

Atur timezone aplikasi di file `.env`:

```env
APP_TIMEZONE=Asia/Jakarta
```

### Locale

Atur bahasa aplikasi di file `.env`:

```env
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
```

## 🧪 Testing

Jalankan automated tests:

```bash
php artisan test
```

Atau dengan PHPUnit langsung:

```bash
./vendor/bin/phpunit
```

## 🐛 Troubleshooting

### Error: "Permission denied" pada storage

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache

# Atau
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Error: "No application encryption key has been specified"

```bash
php artisan key:generate
```

### Error: Database connection failed

Pastikan:
1. Database sudah dibuat (untuk MySQL/PostgreSQL)
2. Kredensial database di `.env` sudah benar
3. Service database sudah berjalan

### Error: npm dependencies

```bash
# Hapus node_modules dan install ulang
rm -rf node_modules package-lock.json
npm install
```

### Error: Vite build issues

```bash
# Clear cache dan rebuild
npm run build -- --force
```

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan:

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Proyek ini menggunakan lisensi [MIT License](https://opensource.org/licenses/MIT).

## 🙏 Credits

- [Laravel Framework](https://laravel.com)
- [Tabler UI](https://tabler.io)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Alpine.js](https://alpinejs.dev)
- [TailwindCSS](https://tailwindcss.com)

## 📞 Kontak & Support

Untuk pertanyaan, saran, atau dukungan, silakan buka [Issue](https://github.com/username/Sistem-Informasi-Arsip-Digital/issues) di repository ini.

---

<p align="center">
  Dibuat dengan ❤️ menggunakan Laravel & Tabler
</p>
