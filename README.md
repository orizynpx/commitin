# CommitIn

## Intro

CommitIn adalah platform web yang memungkinkan mahasiswa untuk menerbitkan lowongan kepanitiaan dan mendaftarkan diri sebagai kandidat panitia kegiatan kemahasiswaan.

CommitIn dapat digunakan oleh mahasiswa, baik secara internal di dalam organisasi kemahasiswaan (ormawa)/unit kegiatan mahasiswa (UKM) maupun untuk mencari mahasiswa sukarelawan (volunteer) dari luar organisasi.

CommitIn dikembangkan sebagai proyek akhir Kelompok 1 mata kuliah Pemrograman Web II Paralel 2 2026.

## Fitur

- **Sistem otentikasi:** Pengguna dapat mendaftarkan akun dan masuk ke sistem sesuai dengan jenis akunnya: `Admin` sistem atau `User` (mahasiswa) biasa.
- **Halaman dashboard:** `User` dapat memantau dan mengelola lowongan kepanitiaan, baik lowongan yang dilamar maupun yang diterbitkan.
- **Manajemen data (CRUD):** Pengguna dapat menerbitkan lowongan kepanitiaan baru (_create_), melihat daftar lowongan (_read_), mengedit lowongan yang ia terbitkan (_update_), dan menghapus lowongan tersebut (_delete_).

## Pengembangan

Seluruh command berikut dijalankan di dalam terminal WSL2. Jangan lupa nyalakan Docker (misalnya melalui Docker Desktop).

1. **Clone repositori:**

```bash
git clone https://github.com/orizynpx/commitin.git && cd commitin
```

2. **Instal dependencies via Docker PHP 8.5:**

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

3. **Salin environment config:**

```bash
cp .env.example .env
```

4. **Nyalakan Laravel Sail (Docker):**

```bash
./vendor/bin/sail up -d
```

5. **Generate key & jalankan migrasi:**

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

6. **Kompilasi aset frontend (Tailwind & Alpine.js):**

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

## Tim Pengembang: Kelompok 1

Pembagian tugas:

- **Hamka Arifani (Database):** Merancang skema basis data.
- **Muhammad Nafis Putra (Frontend):** Merancang tampilan UI dan membuat Blade templates.
- **Noor Muhammad Akmal Sulaiman (Backend & Project Manager):** Mengatur arsitektur proyek, mengembangkan Livewire components, dan membuat logika kode.
