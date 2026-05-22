# MCU — RS Medical Check Up Management System

Sistem informasi Medical Check Up berbasis **Laravel 12 API** + **Vue 3 SPA**. Mengelola alur MCU dari pendaftaran karyawan hingga hasil akhir dalam format PDF.

## Alur End-to-End

```
Karyawan daftar → Admin approve → Dokter periksa fisik
  → Lab input hasil → Radiologi input + upload foto
  → PDF final generated → Karyawan download
```

---

## Peran & Tanggung Jawab

### Admin

Role sentral yang mengelola seluruh operasional:

- **CRUD** Paket MCU (nama, harga, daftar item pemeriksaan)
- **CRUD** User (semua role)
- **Menyetujui/menolak** pendaftaran karyawan
- **Memantau** semua pendaftaran (filter status, search, tanggal)
- **Dashboard statistik** — total pendaftaran, tren 12 bulan, pipeline status, pendaftaran per paket, aktivitas terbaru
- **Export Excel** rekap pendaftaran dengan filter
- **Activity Logs** — track semua perubahan data
- **Akses penuh** ke seluruh data MCU

### Karyawan

Peserta MCU:

- **Mendaftar** MCU — pilih paket, upload KTP, pilih jadwal
- **Melihat histori** pendaftaran sendiri + statusnya
- **Download PDF** hasil MCU setelah selesai
- **Mengupdate profil + signature**

### Dokter Umum

Menangani pemeriksaan fisik:

- **Melihat antrian** pasien yang sudah disetujui admin (hari ini)
- **Input pemeriksaan fisik** — tekanan darah, berat/tinggi badan, IMT, anamnesis, catatan
- **Update** hasil periksa
- **Riwayat** pemeriksaan yang pernah dilakukan
- **Melihat detail** registrasi pasien (data lengkap)

### Laboratorium

Menangani hasil laboratorium:

- **Melihat antrian** pasien yang sudah selesai periksa fisik
- **Input hasil lab** per item pemeriksaan (nilai + keterangan) — item berasal dari paket MCU yang dipilih
- **Update** hasil lab
- **Riwayat** input lab sendiri

### Radiologi

Menangani hasil radiologi + finalisasi:

- **Melihat antrian** pasien yang sudah selesai lab (atau langsung dari dokter jika paket tanpa lab)
- **Input hasil radiologi** — interpretasi + upload foto
- **Generate PDF final** — otomatis setelah input hasil, menggabungkan semua data (identitas, fisik, lab, radiologi, tanda tangan dokter) dalam template PDF RS Juwita
- **Riwayat** hasil radiologi sendiri

---

Setiap role hanya bisa mengakses data sesuai wewenangnya (middleware `role:`), dan semua perubahan tercatat otomatis di **Activity Logs**. Frontend SPA menyesuaikan tampilan per role (routing + navigasi berbeda).
