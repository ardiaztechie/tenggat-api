
# Tenggat API

Backend REST API untuk aplikasi **Tenggat** (Laravel 11, PHP 8.2, MySQL).
Aplikasi Flutter: https://github.com/ardiaztechie/tenggat

## Endpoint
| Method | URL | Keterangan |
|---|---|---|
| GET | `/api/tugas` | Daftar tugas |
| POST | `/api/tugas` | Tambah tugas |
| PUT | `/api/tugas/{id}` | Ubah tugas (termasuk status selesai) |
| DELETE | `/api/tugas/{id}` | Hapus tugas |
| GET | `/api/tugas/statistik` | Ringkasan statistik |
| POST/GET/DELETE | `/api/tugas/{id}/foto` | Unggah / lihat / hapus foto lampiran |

## Menjalankan
1. `composer install`
2. Salin `.env.example` menjadi `.env`, atur koneksi database MySQL, lalu `php artisan key:generate`
3. Buat database, lalu `php artisan migrate`
4. `php artisan serve --host=0.0.0.0 --port=8000`