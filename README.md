# GeoAset Patrol API (Backend)

> **Sistem Informasi Geografis & Monitoring Patroli Aset Daerah**
> *Backend Service.*

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/Database-MySQL-orange?style=flat-square&logo=mysql)
![Status](https://img.shields.io/badge/Status-Development-green?style=flat-square)

## Tentang Projek

**GeoAset Patrol API** adalah layanan backend yang dibangun menggunakan **Laravel 12** untuk mendukung aplikasi mobile dan web monitoring aset. Sistem ini berfokus pada manajemen data spasial (GIS), penugasan petugas lapangan, dan validasi laporan patroli berbasis lokasi (Geofencing).

Sistem ini menerapkan algoritma **Nearest Neighbor** untuk mengoptimalkan rute kunjungan petugas agar efisien secara jarak tempuh.

---

## Fitur Unggulan

### 1. Authentication & Security
* **Laravel Sanctum:** Token-based authentication yang aman untuk Android & Web.
* **Role Management:** Pemisahan hak akses antara **Admin** (Dinas) dan **Officer** (Petugas Lapangan).
* **Email Verification:** Sistem verifikasi email otomatis untuk validasi akun petugas.
* **Secure Credential:** Pengiriman password otomatis via email resmi saat pembuatan akun.

### 2. Geographic Information System (GIS)
* **Asset Management:** CRUD data aset tanah dengan dukungan format **GeoJSON** (Polygon) dan titik koordinat (Centroid).
* **Region Management:** Manajemen batas wilayah (Kota/Kecamatan) dinamis berbasis GeoJSON.
* **Spatial Storage:** Penyimpanan data koordinat presisi tinggi menggunakan MySQL.

### 3. Smart Assignment & Routing (Skripsi Core)
* **Digital Assignment:** Pembuatan surat tugas digital dari Admin ke Petugas.
* **Route Optimization:** Penerapan algoritma **Nearest Neighbor** untuk mengurutkan daftar kunjungan aset berdasarkan jarak terdekat dari posisi awal (Kantor).

### 4. Patrol Validation & Monitoring
* **Radius Validation:** Validasi server-side menggunakan **Haversine Formula** untuk memastikan petugas benar-benar berada di lokasi aset (< 50m) saat melapor.
* **Live Tracking:** Pemantauan posisi petugas yang sedang aktif secara real-time di Dashboard Admin.
* **Photo Evidence:** Upload bukti foto kondisi aset ke cloud storage.

---

## Teknologi yang Digunakan

* **Framework:** Laravel 12
* **Language:** PHP 8.2+
* **Database:** MySQL (dengan dukungan Spatial Data)
* **API Security:** Laravel Sanctum
* **Mail Service:** SMTP (Gmail / Mailtrap)
* **File Storage:** Local Storage (Public Link)

---

## Instalasi & Setup

Ikuti langkah ini untuk menjalankan backend di komputer lokal:

### Prasyarat
* PHP >= 8.2
* Composer
* MySQL

### Langkah-langkah

1.  **Clone Repositori**
    ```bash
    git clone [https://github.com/username-kamu/geoaset-backend.git](https://github.com/username-kamu/geoaset-backend.git)
    cd geoaset-backend
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    ```

3.  **Konfigurasi Environment**
    Duplikat file `.env.example` menjadi `.env`:
    ```bash
    cp .env.example .env
    ```
    Buka file `.env` dan sesuaikan konfigurasi Database & Email:
    ```env
    DB_DATABASE=geoaset_skripsi
    DB_USERNAME=root
    DB_PASSWORD=

    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    # ... konfigurasi email lainnya
    
    QUEUE_CONNECTION=sync  # Agar email terkirim langsung
    ```

4.  **Generate App Key**
    ```bash
    php artisan key:generate
    ```

5.  **Migrasi Database & Seeder**
    Pastikan database `geoaset_skripsi` sudah dibuat di MySQL.
    ```bash
    php artisan migrate --seed
    ```
    *(Command ini akan membuat tabel dan user Super Admin default)*

6.  **Setup Storage Link**
    Wajib dijalankan agar foto aset bisa diakses publik.
    ```bash
    php artisan storage:link
    ```

7.  **Jalankan Server**
    ```bash
    php artisan serve
    ```
    API akan berjalan di: `http://127.0.0.1:8000`

---

## Dokumentasi API (Ringkasan)

Berikut adalah beberapa endpoint utama yang tersedia. Gunakan Postman untuk testing lengkap.

| Method | Endpoint | Deskripsi | Auth |
| :--- | :--- | :--- | :--- |
| **POST** | `/api/login` | Login User & Generate Token | No |
| **POST** | `/api/email/resend` | Kirim ulang verifikasi email | No |
| **GET** | `/api/dashboard` | Statistik & Live Map Admin | **Yes** (Admin) |
| **GET** | `/api/assets` | Ambil semua data aset | **Yes** |
| **POST** | `/api/assignments` | Buat Tugas (+ Auto Optimasi Rute) | **Yes** (Admin) |
| **POST** | `/api/reports` | Kirim Laporan (+ Validasi Radius) | **Yes** (Officer) |
| **POST** | `/api/location/update`| Update Live Location Petugas | **Yes** (Officer) |

---

## Pengujian Algoritma

Algoritma **Nearest Neighbor** terdapat pada class:
`app/Services/RouteOptimizerService.php`

Untuk menguji optimasi rute:
1.  Login sebagai Admin.
2.  Hit endpoint `POST /api/assignments`.
3.  Kirim payload berisi ID Petugas dan Array ID Aset (acak).
4.  Cek response JSON pada bagian `details`. Field `sequence_order` akan otomatis terurut berdasarkan jarak terdekat.

---

## Author

**ElHalc8n**
* Mahasiswa Teknik Informatika
* Universitas Muhammadiyah Pontianak

---

**© 2025 GeoAset Patrol.** All Rights Reserved.
