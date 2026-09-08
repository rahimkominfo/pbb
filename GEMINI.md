buatkan halaman, gunakan struktur codeigniter 4 dan ambil data dari database

Wireframe Akses Admin
+-----------------+-----------------------------------------------------------+
| [ LOGO PBB ]    |  Filter Tahun: [ 2026 (v) ]       Profil: Admin [Logout]  |
+-----------------+-----------------------------------------------------------+
| MENU NAVIGASI   |                                                           |
|                 |  Laporan Capaian & Realisasi PBB                          |
| > Dashboard     |  +-----------------------------------------------------+  |
| > Master Data   |  | Filter Data:                                        |  |
|   - Kolektor    |  | [ Pilih Kecamatan (v) ]  [ Pilih Desa (v) ]         |  |
| > Transaksi     |  +-----------------------------------------------------+  |
|   - Set Target  |                                                           |
|   - Input Setor |  Ringkasan Area Terpilih                                  |
| > Laporan       |  +------------+ +-------------+ +----------+ +---------+  |
| > Pengguna      |  | Target     | | Realisasi   | | Sisa     | | Persen  |  |
|                 |  | Rp 500 Jt  | | Rp 450 Jt   | | Rp 50 Jt | | 90%     |  |
|                 |  +------------+ +-------------+ +----------+ +---------+  |
|                 |                                                           |
|                 |  Tabel Rincian (Fitur Drill-down interaktif)              |
|                 |  | Wilayah / Kolektor | Target | Realisasi | Sisa |  % |  |
|                 |  |--------------------|--------|-----------|------|----|  |
|                 |  | [v] SINJAI BARAT   | 500M   | 450M      | 50M  | 90 |  |
|                 |  |   [-] Desa A       | 200M   | 200M      | 0    | 100|  |
|                 |  |      - Kol. Andi   | 100M   | 100M      | 0    | 100|  |
|                 |  |      - Kol. Budi   | 100M   | 100M      | 0    | 100|  |
|                 |  |   [+] Desa B       | 300M   | 250M      | 50M  | 83 |  |
+-----------------+-----------------------------------------------------------+

Penjelasan Perubahan:

Navigasi Lebih Rapi: Sidebar kini lebih bersih. Penghapusan menu Master Kecamatan dan Desa biasanya dilakukan jika data wilayah tersebut diasumsikan statis (sudah di-seed di dalam database dan jarang sekali ada penambahan kecamatan/desa baru), sehingga admin tidak perlu menu khusus untuk menambah atau mengubah data tersebut setiap saat.

Kolektor Tetap Ada: Menu Master Kolektor umumnya tetap dipertahankan karena pergantian atau penambahan nama kolektor di lapangan lebih dinamis dan sering terjadi.


Wireframe: Master Kolektor
+-----------------+-----------------------------------------------------------+
| [ LOGO PBB ]    |                                   Profil: Admin [Logout]  |
+-----------------+-----------------------------------------------------------+
| MENU NAVIGASI   |  Manajemen Data Kolektor                                  |
|                 |  +-----------------------------------------------------+  |
| > Dashboard     |  | [ Cari Nama Kolektor... ]   [ Filter Kecamatan v ]  |  |
| > Master Data   |  |                             [ + Tambah Kolektor  ]  |  |
|   - Kolektor (v)|  +-----------------------------------------------------+  |
| > Transaksi     |                                                           |
|   - Set Target  |  | No | Kode | Nama Kolektor   | Desa (Kecamatan) | Aksi| |
|   - Input Setor |  |----|------|-----------------|------------------|-----| |
| > Laporan       |  | 1  | 01   | MUH. YUSUF      | Gn. Perak (Barat)| [E][H]|
| > Pengguna      |  | 2  | 02   | SALAHUDDIN      | Gn. Perak (Barat)| [E][H]|
|                 |  | 3  | 01   | RISKAWATI       | Balakia (Barat)  | [E][H]|
|                 |                                                           |
|                 |  [< Prev]  Halaman 1 dari 10  [Next >]                    |
+-----------------+-----------------------------------------------------------+

* Keterangan Modal/Form Tambah Kolektor:
  - Dropdown: Pilih Kecamatan -> Memfilter Dropdown Pilih Desa (desa_id)
  - Input Text: Kode Kolektor (kd_kolektor - 2 digit)
  - Input Text: Nama Kolektor (nm_kolektor)
  - Input Text: Dusun (opsional)


Wireframe: Set Target (Transaksi)
+-----------------+-----------------------------------------------------------+
| [ LOGO PBB ]    |                                   Profil: Admin [Logout]  |
+-----------------+-----------------------------------------------------------+
| MENU NAVIGASI   |  Penetapan Target PBB Desa                                |
|                 |  +-----------------------------------------------------+  |
| > Dashboard     |  | Filter: [ Tahun 2026 v ] [ Kec. Sinjai Barat v ]    |  |
| > Master Data   |  |                             [ + Set Target Baru  ]  |  |
|   - Kolektor    |  +-----------------------------------------------------+  |
| > Transaksi (v) |                                                           |
|   - Set Target  |  | No | Desa (Kecamatan)      | Tahun | Jumlah Target | Aksi|
|   - Input Setor |  |----|-----------------------|-------|---------------|-----|
| > Laporan       |  | 1  | Gunung Perak (Barat)  | 2026  | Rp 55.120.124 | [E] |
| > Pengguna      |  | 2  | Balakia (Barat)       | 2026  | Rp 28.391.835 | [E] |
|                 |  | 3  | Botolempangang (Barat)| 2026  | Rp 51.091.525 | [E] |
|                 |                                                           |
|                 |  [< Prev]  Halaman 1 dari 5  [Next >]                     |
+-----------------+-----------------------------------------------------------+

* Keterangan Modal/Form Set Target Baru:
  - Dropdown: Pilih Tahun (contoh: 2026)
  - Dropdown: Pilih Desa (desa_id) - Bisa dibuat searchable dropdown (Select2)
  - Input Number: Jumlah Target (Rp)

Wireframe: Input Setor (Transaksi)
+-----------------+-----------------------------------------------------------+
| [ LOGO PBB ]    |                                   Profil: Admin [Logout]  |
+-----------------+-----------------------------------------------------------+
| MENU NAVIGASI   |  Riwayat Input Setoran (Realisasi)                        |
|                 |  +-----------------------------------------------------+  |
| > Dashboard     |  | [ Cari Nama Kolektor... ]   [ Filter Bulan/Thn v ]  |  |
| > Master Data   |  |                             [ + Tambah Setoran   ]  |  |
|   - Kolektor    |  +-----------------------------------------------------+  |
| > Transaksi (v) |                                                           |
|   - Set Target  |  | No | Tgl Bayar  | Nama Kolektor (Desa) | Nominal Setor |
|   - Input Setor |  |----|------------|----------------------|---------------|
| > Laporan       |  | 1  | 17-07-2026 | MUH. YUSUF (Gn. Perak)| Rp 2.500.000 |
| > Pengguna      |  | 2  | 16-07-2026 | RISKAWATI (Balakia)  | Rp 1.000.000  |
|                 |  | 3  | 15-07-2026 | UMAR (P. Buhung Pitue)| Rp   500.000 |
|                 |                                                           |
|                 |  [< Prev]  Halaman 1 dari 20  [Next >]                    |
+-----------------+-----------------------------------------------------------+

* Keterangan Modal/Form Tambah Setoran:
  - Input Date: Tanggal Bayar (tgl_bayar)
  - Searchable Dropdown: Pilih Kolektor (kolektor_id) -> Tampilkan "Nama Kolektor - Desa" agar tidak bingung jika ada nama sama.
  - Input Number: Nilai Setoran Realisasi (Rp)
  (Catatan Logika: Saat setoran disimpan, sistem harus menjumlahkannya ke kolom `jml_realisasi` di tabel `trn_target` untuk desa terkait).

Wireframe: Pengguna (Sistem)
+-----------------+-----------------------------------------------------------+
| [ LOGO PBB ]    |                                   Profil: Admin [Logout]  |
+-----------------+-----------------------------------------------------------+
| MENU NAVIGASI   |  Manajemen Akun Pengguna                                  |
|                 |  +-----------------------------------------------------+  |
| > Dashboard     |  | [ Cari Username... ]                                |  |
| > Master Data   |  |                             [ + Tambah Pengguna  ]  |  |
|   - Kolektor    |  +-----------------------------------------------------+  |
| > Transaksi     |                                                           |
|   - Set Target  |  | No | Username        | Role Akses    | Aksi            |
|   - Input Setor |  |----|-----------------|---------------|-----------------|
| > Laporan       |  | 1  | rahim           | Admin         | [Reset Pass][H] |
| > Pengguna (v)  |  | 2  | staf_barat      | User          | [Reset Pass][H] |
|                 |  | 3  | operator1       | User          | [Reset Pass][H] |
|                 |                                                           |
+-----------------+-----------------------------------------------------------+

* Keterangan Modal/Form Tambah Pengguna:
  - Input Text: Username (Pastikan divalidasi harus unik)
  - Input Password: Password
  - Select Dropdown: Role (Admin / User)

* Keterangan Aksi [Reset Pass]:
  Sebuah modal kecil untuk meng-override password lama dengan password baru (kemudian di-hash menggunakan bcrypt/password_hash sebelum masuk ke database).




Catatan Desain & UX Tambahan:

Tombol Aksi: Gunakan ikon FontAwesome pada folder assets/fontawesome untuk tombol aksi pada tabel. [E] = Ikon Pensil (Edit), [H] = Ikon Tempat Sampah (Hapus/Delete).

Pencarian Kolektor (Input Setor): Karena jumlah kolektor cukup banyak (ratusan), sangat disarankan dropdown pemilihan kolektor pada menu "Input Setor" menggunakan plugin seperti Select2 atau Alpine/Livewire searchable dropdown, agar admin bisa mengetikkan nama kolektor tanpa harus scroll panjang.