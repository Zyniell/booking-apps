# 📅 Aplikasi Booking & Reservasi

Aplikasi web pemesanan dan reservasi layanan berbasis Laravel dengan sistem pemilihan slot waktu real-time, integrasi gateway pembayaran Midtrans, notifikasi WhatsApp otomatis melalui Fonnte, serta panel admin modern bertenaga Filament.

---

## 🛠️ 1. Tech Stack yang Dipakai

### **Backend & Framework**
- **PHP**: `^8.2`
- **Laravel Framework**: `^12.0`
- **Livewire**: `^3.6` (Reaktivitas komponen antarmuka, pemilihan tanggal & slot waktu booking secara dinamis tanpa reload halaman)

### **Frontend & Styling**
- **Blade Templating Engine**
- **Tailwind CSS**: `^4.0`
- **Vite**: `^7.0` (Asset bundler modern & cepat)

### **Admin Panel**
- **Filament PHP**: `^4.1`
  - Dashboard statistik & ringkasan booking
  - Manajemen Item / Produk Reservasi
  - Manajemen Kategori
  - Manajemen Data Transaksi Booking
  - Konfigurasi Usaha (Jam operasional, jenis booking: per jam, per 12 jam, per hari, atau custom)

### **Database**
- **SQLite** (Konfigurasi default siap pakai)
- Mendukung juga **MySQL / MariaDB** (via Laragon, XAMPP, atau Docker)

### **Third-Party & API Integrations**
- **Midtrans Snap (`midtrans/midtrans-php` ^2.6)**: Integrasi gateway pembayaran online (Sandbox & Production).
- **Fonnte API**: WhatsApp Gateway untuk pengiriman notifikasi konfirmasi booking otomatis ke nomor WhatsApp pelanggan.

---

## 🚀 2. Cara Menjalankan di Local

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek di lingkungan pengembangan lokal:

### **A. Prasyarat Sistem (Prerequisites)**
Pastikan di perangkat Anda telah terpasang:
- **PHP**: Versi `>= 8.2` (ekstensi aktif: `pdo_sqlite` / `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`)
- **Composer**: Versi `>= 2.x`
- **Node.js**: Versi `>= 18.x` dan **NPM**
- Web development environment seperti **Laragon** (opsional, disarankan di Windows)

---

### **B. Langkah-Langkah Instalasi**

#### 1. Masuk ke Direktori Proyek
Buka terminal dan navigasi ke direktori proyek:
```bash
cd c:/laragon/www/booking-app
```

#### 2. Install Dependensi PHP (Composer)
```bash
composer install
```

#### 3. Install Dependensi Frontend (NPM)
```bash
npm install
```

#### 4. Siapkan File Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
- **Windows (PowerShell / CMD):**
  ```powershell
  copy .env.example .env
  ```
- **Linux / Git Bash:**
  ```bash
  cp .env.example .env
  ```

#### 5. Generate Application Key
```bash
php artisan key:generate
```

#### 6. Konfigurasi Database
Secara default, aplikasi menggunakan database **SQLite**.

- **Jika Menggunakan SQLite (Default):**
  Pastikan berkas `database/database.sqlite` sudah ada. Jika belum, buat dengan perintah:
  - *PowerShell:*
    ```powershell
    New-Item -ItemType File database/database.sqlite -Force
    ```
  - *Git Bash / Linux:*
    ```bash
    touch database/database.sqlite
    ```

- **Jika Menggunakan MySQL / MariaDB (Laragon / XAMPP):**
  Buat database baru (misal: `booking_app`) di phpMyAdmin / HeidiSQL, lalu sesuaikan konfigurasi di file `.env`:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=booking_app
  DB_USERNAME=root
  DB_PASSWORD=
  ```

#### 7. Konfigurasi API Eksternal (Opsional)
Buka file `.env` dan tambahkan kredensial jika ingin menguji fitur pembayaran dan notifikasi WhatsApp:
```env
# Midtrans Payment Gateway
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false

# Fonnte WhatsApp Gateway
FONNTE_TOKEN=token_fonnte_anda
```

#### 8. Jalankan Migrasi Database
Jalankan migrasi skema tabel ke database:
```bash
php artisan migrate
```
*(Opsional: Jalankan seeder dengan `php artisan db:seed` jika ingin membuat data awal).*

#### 9. Buat Akun Admin Filament
Untuk dapat masuk ke panel admin, buat user admin baru:
```bash
php artisan make:filament-user
```
> Masukkan **Name**, **Email**, dan **Password** sesuai petunjuk di terminal.

---

### **C. Menjalankan Server Lokal**

Jalankan backend Laravel dan frontend Vite. Anda dapat membuka 2 tab terminal terpisah:

**Terminal 1 (Laravel Development Server):**
```bash
php artisan serve
```

**Terminal 2 (Vite Asset Server):**
```bash
npm run dev
```

> 💡 **Alternatif:** Anda juga dapat menjalankan seluruh service (Server, Queue, Logs, & Vite) secara bersamaan menggunakan script bawaan:
> ```bash
> composer run dev
> ```

---

### **D. Mengakses Aplikasi**

Setelah server berjalan, buka browser dan akses URL berikut:

- 🌐 **Halaman Utama / Katalog Pemesanan Pelanggan:**  
  [http://127.0.0.1:8000](http://127.0.0.1:8000)

- 🔒 **Panel Admin Filament:**  
  [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)  
  *(Login menggunakan email dan password admin yang telah dibuat pada langkah ke-9)*
