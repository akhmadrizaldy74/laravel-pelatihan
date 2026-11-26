# Dashboard Jadwal & Kalender Pelatihan — PPSDMK Kemnaker Corpu

Aplikasi web manajemen jadwal pelatihan tahunan, kalender Gantt interaktif, ekspor/impor Excel, serta portal informasi publik PPSDMK — Kemnaker Corporate University.

## Fitur Utama

- **Dashboard Publik (`/`)**: Daftar pelatihan per tahun, filter kategori/status/lokasi/dana, ringkasan kuota & realisasi peserta, kalender Gantt tahunan (dapat ditampilkan/disembunyikan oleh admin), cetak PDF, dan unduh Excel.
- **Panel Admin (`/admin`)**: Manajemen pelatihan multi-tahapan pembelajaran, upload Excel & template siap isi, mode Draft/Tampil untuk penyusunan rencana tahun berikutnya (termasuk salin rencana dari tahun sebelumnya), deteksi bentrok hari libur, rekap analitik, master data (kategori, metode, hari libur), serta manajemen akun & audit log.

## Kebutuhan Sistem

- PHP `>= 8.2` (ekstensi `zip`, `xml`, `gd`, `mbstring`, `pdo_mysql`)
- Composer
- MySQL / MariaDB

## Cara Menjalankan Project (Setelah `git clone`)

1. Masuk ke folder project dan install dependency PHP:
   ```bash
   composer install
   ```
2. Salin file environment dan generate application key:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
   *(Di macOS/Linux gunakan `cp .env.example .env`.)*
3. Buat database MySQL bernama `db_pelatihan` (atau sesuaikan `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di file `.env`).
4. Jalankan migrasi dan data awal (seeder):
   ```bash
   php artisan migrate --seed
   ```
5. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
   Buka di browser: `http://127.0.0.1:8000` (Publik) atau `http://127.0.0.1:8000/admin` (Admin).

## Akun Admin Default (Dari Seeder)

- **Email**: `admin@pelatihan.com`
- **Password**: `admin123`

*(Segera ubah password atau buat akun admin baru melalui menu **Akun & Log** setelah login pertama kali.)*

## Menjalankan Pengujian

```bash
php artisan test
```
