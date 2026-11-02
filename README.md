# PKKM — Penilaian Kinerja Kepala Madrasah

Aplikasi web mandiri berbasis **PHP Native MVC**, **Tailwind CSS**, dan **MySQL** untuk mengelola, menilai, serta memantau kinerja Kepala Madrasah secara terintegrasi, transparan, dan akuntabel sesuai standar pedoman Kementerian Agama RI.

---

## Fitur Utama

- **5 Komponen Baku & 108 Butir Indikator:**
  - Komponen 1: Usaha Pengembangan Madrasah
  - Komponen 2: Pelaksanaan Tugas Manajerial
  - Komponen 3: Pengembangan Kewirausahaan
  - Komponen 4: Supervisi Guru dan Tenaga Kependidikan
  - Komponen 5: Hasil Kinerja Kepala Madrasah
- **Snapshot Instrumen Per Periode:** Perubahan data master tidak merusak riwayat penilaian periode lampau.
- **Multi-Penilai 6 Kelompok:**
  1. KS (Kepala Seksi / Penilai 1) — Bobot 4
  2. KY (Ketua Yayasan / Penilai 2) — Bobot 2
  3. WASMAD (Pengawas Madrasah) — Bobot 1
  4. GURU (Perwakilan Guru) — Bobot 1
  5. TENDIK (Tenaga Kependidikan) — Bobot 1
  6. KOMITE (Komite Madrasah) — Bobot 1
- **Kalkulasi Otomatis NKTE (Nilai Kinerja Empat Tahunan):**
  $$\text{NKTE} = \frac{4(\text{KS}) + 2(\text{KY}) + \text{Wasmad} + \text{Guru} + \text{Tendik} + \text{Komite}}{10}$$
- **Manajemen Bukti Dukung Fisik:** Unggah dan pantau berkas autentik langsung pada masing-masing butir indikator per komponen.
- **Audit Log Lengkap:** Catatan riwayat login, input nilai, mutasi data, dan aktivitas penting sistem.
- **Ekspor Laporan:** Rekapitulasi hasil penilaian ke format PDF dan Excel.
- **Tampilan Modern:** Font Inter, tema Tailwind CSS responsif, modal anti-overflow, dan segmented control scoring.

---

## Arsitektur & Struktur Direktori

```text
pkkm/
├── app/
│   ├── Controllers/         # Controller modul (Admin, Penilaian, Bukti, Report, Auth)
│   ├── Core/                # Router, Auth guard, CSRF, helpers
│   └── Services/            # CalculationService (NKTE formula)
├── config/
│   └── database.php         # PDO connection & credential
├── database/
│   ├── migrate.php          # Migrasi skema database (22 tabel)
│   ├── seed_master_instrumen.php
│   └── seed_users_and_period.php
├── public/                  # Document root
│   ├── index.php            # Front controller & routing
│   └── uploads/             # Direktori berkas bukti dukung
├── views/                   # Tampilan antarmuka Blade-style PHP
│   ├── admin/               # CRUD Periode, Madrasah, Kepala, Pengguna, Instrumen, Audit
│   ├── auth/                # Halaman masuk
│   ├── bukti/               # Matriks bukti dukung 5 komponen
│   ├── dashboard/           # Dashboard Admin, Kamad, Penilai
│   ├── layout/              # Header, Topbar, Sidebar, Footer
│   ├── penilaian/           # Lembar penilaian dinamis skor 1-4
│   └── report/              # Rekapitulasi & lembar pengesahan
├── .gitignore
├── composer.json
├── README.md
└── sync_backup.bat          # Script commit, push & backup zip
```

---

## Kebutuhan Sistem

- PHP >= 8.1 (ekstensi: `pdo_mysql`, `mbstring`, `fileinfo`, `zip`)
- MySQL >= 5.7 atau MariaDB >= 10.3
- Apache / Nginx
- Composer (untuk Dompdf & PhpSpreadsheet)

---

## Cara Instalasi

1. **Clone repositori:**
   ```bash
   git clone https://github.com/dewecorp/pkkm.git
   cd pkkm
   ```

2. **Pasang dependensi vendor:**
   ```bash
   composer install
   ```

3. **Konfigurasi Database:**
   Sesuaikan kredensial di `config/database.php`:
   ```php
   'host' => '127.0.0.1',
   'dbname' => 'pkkm_db',
   'username' => 'root',
   'password' => '',
   ```

4. **Jalankan Migrasi & Seeder Awal:**
   ```bash
   php database/migrate.php
   php database/seed_master_instrumen.php
   php database/seed_users_and_period.php
   ```

5. **Akses Aplikasi:**
   - URL: `http://localhost/pkkm/public` (atau via virtual host)

---

## Akun Bawaan (Default Credentials)

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Kepala Madrasah | `kamad` | `kamad123` |
| Penilai KS (Kasi Madrasah) | `penilai_ks` | `penilai123` |
| Penilai KY (Ketua Yayasan) | `penilai_ky` | `penilai123` |
| Penilai Wasmad (Pengawas) | `penilai_wasmad` | `penilai123` |
| Penilai Guru | `penilai_guru` | `penilai123` |
| Penilai Tendik | `penilai_tendik` | `penilai123` |
| Penilai Komite | `penilai_komite` | `penilai123` |

---

## Otomatisasi Git & Backup

Gunakan file script yang disediakan:
- Dobel klik file **`sync_backup.bat`** di root direktori untuk langsung commit, push ke repo `https://github.com/dewecorp/pkkm.git`, dan membuat arsip `pkkm_backup.zip`.
