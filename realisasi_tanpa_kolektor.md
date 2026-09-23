# Rancangan Penanganan Realisasi Tanpa Kolektor (Berbasis Desa)
**Sistem Informasi Pengelolaan Realisasi & Target PBB-P2**  
*Dokumen Rencana Teknis & Analisis Arsitektur Database dan Aplikasi*

---

## 1. Ringkasan Eksekutif

Dalam operasional penerimaan Pajak Bumi dan Bangunan (PBB-P2), terdapat penerimaan/setoran realisasi yang tidak melalui petugas kolektor perorangan, melainkan:
1. Pembayaran langsung oleh Wajib Pajak melalui loket Bank / ATM / QRIS / Kas Daerah.
2. Setoran kolektif langsung oleh Perangkat / Bendahara Desa.
3. Data historis/migrasi DSH (Daftar Surat Hasil) yang tercatat atas nama Desa namun belum/tidak memiliki atribusi kolektor spesifik.

Saat ini tabel transaksi `trn_realisasi_dsh` memiliki batasan integritas di mana kolom `kolektor_id` berstatus `NOT NULL` dengan *Foreign Key* ke tabel `mst_kolektor`. Kondisi ini menyebabkan data realisasi yang hanya memiliki informasi Desa (`desa_id`) tidak dapat disimpan ke database tanpa "memaksa" memilih salah satu kolektor.

Dokumen ini merancang solusi teknis yang tepat, aman, dan berkesinambungan agar realisasi tersebut tetap tercatat, masuk ke perhitungan capaian Desa/Kecamatan, namun tidak merusak validitas data master kolektor maupun perhitungan insentif.

---

## 2. Analisis Kondisi Saat Ini (Current State)

### 2.1 Skema Tabel Saat Ini
```sql
CREATE TABLE `trn_realisasi_dsh` (
  `realisasi_dsh_id` int NOT NULL AUTO_INCREMENT,
  `kolektor_id`      int NOT NULL,                     -- WAJIB DIISI (NOT NULL)
  `tahun`            int NOT NULL DEFAULT '2026',
  `jml_op`           int NOT NULL DEFAULT '0',
  `realisasi`        bigint NOT NULL,
  `tgl_bayar`        date DEFAULT NULL,
  `created_at`       datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`realisasi_dsh_id`),
  CONSTRAINT `fk_trn_realisasi_dsh_kolektor` 
    FOREIGN KEY (`kolektor_id`) REFERENCES `mst_kolektor` (`kolektor_id`)
) ENGINE=InnoDB;
```

### 2.2 Keterbatasan Arsitektur Saat Ini
1. **Tidak Memiliki Kolom `desa_id` Mandiri**:
   Identitas Desa dari suatu setoran diperoleh secara *tidak langsung* melalui relasi:
   $$\text{trn\_realisasi\_dsh} \longrightarrow \text{mst\_kolektor} \longrightarrow \text{mst\_desa}$$
2. **Ketergantungan Penuh pada `kolektor_id`**:
   Jika setoran belum/tidak memiliki kolektor, sistem menolak data (`Integrity constraint violation: Column 'kolektor_id' cannot be null`).
3. **Dilema di Lapangan**:
   Jika admin dipaksa memilih sembarang kolektor yang ada di desa tersebut:
   - Data kinerja kolektor tersebut menjadi tidak akurat (terlalu tinggi).
   - Perhitungan insentif kolektor pada menu **Laporan Insentif** menjadi tidak adil karena kolektor tersebut menerima bonus atas setoran yang bukan hasil kerjanya.

---

## 3. Evaluasi Opsi Solusi

Terdapat 2 (dua) opsi pendekatan arsitektur untuk menyelesaikan persoalan ini:

| Parameter | Opsi A: Restrukturisasi Tabel (Rekomendasi Utama) | Opsi B: Kolektor Sistem / Virtual ("Non-Kolektor") |
| :--- | :--- | :--- |
| **Deskripsi** | Menambahkan `desa_id` di `trn_realisasi_dsh` dan membuat `kolektor_id` bernilai `NULLABLE` (*opsional*). | Membuat 1 entri kolektor khusus bertitel *"Non-Kolektor / Kas Desa"* pada `mst_kolektor` di setiap desa. |
| **Perubahan Skema DB** | Ya (tambah kolom `desa_id`, relaksasi `kolektor_id` menjadi `NULL`). | Tidak ada perubahan skema database. |
| **Integritas Relasi** | Sangat baik; mencerminkan model bisnis sebenarnya (setoran milik Desa, kolektor bersifat pelaksana opsional). | Artifisial/Semu; mengotori tabel Master Kolektor dengan data fiktif. |
| **Laporan Insentif** | Bersih; setoran tanpa kolektor otomatis tidak terhitung ke insentif kolektor perseorangan. | Rawan salah; kolektor virtual akan ikut terdaftar dan harus di-filter manual dengan hardcode pengecualian. |
| **Performa Query** | Jauh lebih cepat; agregasi realisasi per Desa/Kecamatan tidak perlu lagi `JOIN` ke tabel kolektor. | Tetap memerlukan `JOIN mst_kolektor`. |
| **Keluwesan Data** | Fleksibel; setoran bisa di-input dulu, dan jika di kemudian hari diketahui kolektornya, tinggal di-edit/di-assign. | Kaku; harus memindahkan relasi antar ID kolektor. |

> [!IMPORTANT]  
> **Rekomendasi Terbaik: OPSI A (Restrukturisasi Skema Database)**.  
> Opsi A merupakan *best practice* perancangan basis data relasional untuk sistem perpajakan/retribusi daerah, di mana objek target dan realisasi primer berinduk pada **Wilayah Administratif (Desa)**, sedangkan **Kolektor** adalah atribut sekunder (petugas penagih).

---

## 4. Rincian Teknis Perbaikan (Opsi Rekomendasi A)

### 4.1 Perubahan Skema Basis Data

Tabel `trn_realisasi_dsh` diubah sehingga:
1. Memiliki kolom `desa_id` (`INT NOT NULL`), berelasi ke `mst_desa(desa_id)`.
2. Kolom `kolektor_id` diubah menjadi `INT NULL` (boleh kosong).
3. Relasi *Foreign Key* `kolektor_id` diubah menjadi `ON DELETE SET NULL`.

#### Skrip DDL (MySQL):
```sql
-- 1. Tambah kolom desa_id setelah realisasi_dsh_id
ALTER TABLE `trn_realisasi_dsh` 
  ADD COLUMN `desa_id` INT NOT NULL AFTER `realisasi_dsh_id`;

-- 2. Migrasi data desa_id dari mst_kolektor untuk data yang sudah ada (jika ada)
UPDATE `trn_realisasi_dsh` r
  JOIN `mst_kolektor` k ON r.kolektor_id = k.kolektor_id
  SET r.desa_id = k.desa_id;

-- 3. Ubah kolom kolektor_id menjadi NULLABLE
ALTER TABLE `trn_realisasi_dsh` 
  MODIFY COLUMN `kolektor_id` INT NULL;

-- 4. Tambahkan Foreign Key desa_id dan indeks
ALTER TABLE `trn_realisasi_dsh`
  ADD CONSTRAINT `fk_trn_realisasi_dsh_desa` 
    FOREIGN KEY (`desa_id`) REFERENCES `mst_desa` (`desa_id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD INDEX `idx_trn_realisasi_dsh_desa` (`desa_id`);

-- 5. Perbarui Foreign Key kolektor_id agar mendukung SET NULL saat kolektor dihapus
ALTER TABLE `trn_realisasi_dsh` 
  DROP FOREIGN KEY `fk_trn_realisasi_dsh_kolektor`;

ALTER TABLE `trn_realisasi_dsh` 
  ADD CONSTRAINT `fk_trn_realisasi_dsh_kolektor` 
    FOREIGN KEY (`kolektor_id`) REFERENCES `mst_kolektor` (`kolektor_id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;
```

#### File Migrasi CodeIgniter 4:
Dibuatkan file migrasi otomatis:  
`app/Database/Migrations/2026-09-23-000002_AddDesaIdAndNullableKolektorToTrnRealisasiDsh.php`

---

### 4.2 Perubahan Alur Input Setoran (Form Setoran)

Pada form input setoran (baik tambah maupun edit) di [`app/Views/admin/setor.php`](file:///var/www/html/pbb_ar/app/Views/admin/setor.php):

```
+-------------------------------------------------------------------+
| MODAL FORM: Catat Setoran Baru                                    |
+-------------------------------------------------------------------+
| [ Tahun Pajak: 2026 ]        [ Tanggal Bayar: 23-09-2026 ]        |
|                                                                   |
| [ Pilih Kecamatan: Sinjai Barat                      (v) ]        |
| [ Pilih Desa: Gunung Perak                           (v) ] *Wajib |
|                                                                   |
| [ Pilih Kolektor: -- Tanpa Kolektor / Setoran Langsung -- (v) ]   |
|   *Opsional: Hanya menampilkan kolektor yang terdaftar di Desa    |
|                                                                   |
| [ Jumlah OP: 15     ]                                             |
| [ Nominal Setor: Rp 2.500.000 ]                                   |
|                                                                   |
|                             [ Batal ]  [ Simpan Setoran ]         |
+-------------------------------------------------------------------+
```

#### Alur UX (User Experience):
1. Pengguna memilih **Tahun** dan **Tanggal Bayar**.
2. Pengguna memilih **Kecamatan** lalu **Desa** (desa_id wajib diisi).
3. Dropdown **Kolektor** akan terfilter secara dinamis menampilkan kolektor di desa tersebut untuk tahun terkait, dengan opsi default paling atas:
   `-- Tanpa Kolektor (Setoran Desa/Langsung) --` (nilai: empty/NULL).
4. Jika disetorkan oleh kolektor tertentu, admin cukup memilih nama kolektornya. Jika setoran dari bank/kas desa, admin membiarkannya kosong.

---

### 4.3 Perubahan Backend Controller & Query Logic

#### A. Penyimpanan Setoran (`saveSetor` di `app/Controllers/Admin.php`)
```php
$desaId     = $this->request->getPost('desa_id');
$kolektorId = $this->request->getPost('kolektor_id') ?: null; // null jika tidak dipilih
$tahun      = (int)$this->request->getPost('tahun');
$nominal    = (float)$this->request->getPost('realisasi');
$jmlOp      = (int)$this->request->getPost('jml_op');
$tglBayar   = $this->request->getPost('tgl_bayar');

// Jika kolektor dipilih tapi desa_id tidak dikirim, ambil desa_id dari kolektor
if (empty($desaId) && !empty($kolektorId)) {
    $col = $this->db->table('mst_kolektor')->where('kolektor_id', $kolektorId)->get()->getRowArray();
    $desaId = $col['desa_id'] ?? null;
}

// Validasi: desa_id, tahun, nominal, tgl_bayar, jml_op wajib ada
// kolektor_id boleh null
if (empty($desaId) || empty($tglBayar) || empty($nominal) || empty($tahun)) {
    return redirect()->back()->with('error', 'Desa, Tanggal, Tahun, dan Nominal wajib diisi!');
}

// Simpan data setoran
$data = [
    'desa_id'     => $desaId,
    'kolektor_id' => $kolektorId, // bernilai NULL atau integer ID kolektor
    'tahun'       => $tahun,
    'jml_op'      => $jmlOp,
    'realisasi'   => $nominal,
    'tgl_bayar'   => $tglBayar
];

// Akumulasi realisasi ke trn_target desa terkait tetap berjalan 100% akurat:
// desa_id sudah pasti diketahui langsung dari input!
```

#### B. Query Agregasi Realisasi Desa & Kecamatan (Lebih Cepat & Sederhana)
Sebelumnya query realisasi desa harus melewati `mst_kolektor`:
```sql
-- QUERY LAMA (Setoran tanpa kolektor TIDAK AKAN TERHITUNG):
SELECT SUM(r.realisasi) 
FROM trn_realisasi_dsh r 
JOIN mst_kolektor c ON r.kolektor_id = c.kolektor_id 
WHERE c.desa_id = :desa_id AND r.tahun = :tahun;
```

Diperbarui menjadi query langsung berbasis `r.desa_id`:
```sql
-- QUERY BARU (Semua setoran baik ada kolektor maupun tanpa kolektor PASTI TERHITUNG):
SELECT SUM(r.realisasi) 
FROM trn_realisasi_dsh r 
WHERE r.desa_id = :desa_id AND r.tahun = :tahun;
```

---

### 4.4 Dampak pada Tampilan & Laporan

#### 1. Tabel Riwayat Setoran (`admin/setor`)
- Pada kolom **Nama Kolektor (Desa)**:
  - Jika `kolektor_id` ada: Menampilkan nama kolektor beserta badge desa seperti biasa (contoh: `MUH. YUSUF (Gn. Perak)`).
  - Jika `kolektor_id` kosong/NULL: Menampilkan badge penanda khusus berwarna abu-abu/amber:  
    `[Tanpa Kolektor / Setoran Langsung]` dan tetap mencantumkan nama Desanya (contoh: `Setoran Langsung (Gn. Perak)`).

#### 2. Dashboard & Fitur Drill-down Hierarkis
Pada tabel drilldown interaktif di Dashboard:
```
Kecamatan / Desa / Kolektor             | Target     | Realisasi  | Sisa      | %
----------------------------------------+------------+------------+-----------+-----
[v] SINJAI BARAT                        | Rp 500 Jt  | Rp 450 Jt  | Rp 50 Jt  | 90%
  [-] Desa Gunung Perak                 | Rp 200 Jt  | Rp 200 Jt  | Rp 0      | 100%
      - MUH. YUSUF (Kolektor)           | -          | Rp 100 Jt  | -         | -
      - SALAHUDDIN (Kolektor)           | -          | Rp  70 Jt  | -         | -
      - *Setoran Langsung / Non-Kolektor* | -        | Rp  30 Jt  | -         | -
```
Dengan cara ini:
- Total realisasi Desa tetap `Rp 200 Jt` (akurat 100%).
- Penjumlahan seluruh sub-baris di bawah desa sama persis dengan total desa ($100 + 70 + 30 = 200$).

#### 3. Laporan Insentif Upah Pungut
- **Tab Camat & Tab Kades**: Dihitung dari total realisasi desa (`r.desa_id`), sehingga realisasi tanpa kolektor **tetap dihitung** penuh sebagai prestasi capaian wilayah kades dan camat.
- **Tab Kolektor**: Hanya menghitung realisasi dari setoran yang memiliki `kolektor_id` valid, sehingga alokasi insentif per orangan kolektor tidak salah bayar.

---

## 5. Rencana Tahapan Eksekusi (Implementation Steps)

Jika rancangan ini disetujui, langkah implementasi adalah sebagai berikut:

1. **Tahap 1: Migrasi Database**
   - Menjalankan migrasi CI4 untuk menambahkan kolom `desa_id` dan mengubah `kolektor_id` menjadi `NULL`.
   - Mengisi otomatis nilai `desa_id` pada transaksi lama berdasarkan data kolektor eksisting.
   
2. **Tahap 2: Refactoring Backend Model & Controller**
   - Memperbarui `Admin::saveSetor()` untuk menerima `desa_id` dan mengizinkan `kolektor_id` kosong (`null`).
   - Memperbarui query akumulasi target dan drilldown pada `Admin::dashboard()`.
   - Memperbarui query agregasi pada `Admin::target()` dan `TargetModel::getRealisasiPerKecamatan()`.
   - Memperbarui `Admin::insentif()` dan `Admin::exportInsentif()`.

3. **Tahap 3: Penyempurnaan Tampilan (View)**
   - Menambahkan dropdown cascading (Kecamatan -> Desa -> Kolektor) pada Modal Input Setoran di `setor.php`.
   - Mengakomodasi tampilan baris tanpa kolektor di tabel data setoran.
   - Mengakomodasi baris *Non-Kolektor / Setoran Langsung* pada drilldown pohon hierarki di Dashboard.

4. **Tahap 4: Pengujian & Verifikasi**
   - Uji input setoran baru dengan kolektor.
   - Uji input setoran baru **tanpa kolektor** (hanya pilih desa).
   - Verifikasi sinkronisasi angka ke `trn_target` dan dashboard capaian.

---

## 6. Kesimpulan

Dengan menerapkan **Opsi A** (penambahan `desa_id` dan relaksasi `kolektor_id` menjadi `NULLABLE`):
1. Masalah setoran yang belum/tidak memiliki kolektor terselesaikan secara elegan tanpa perlu manipulasi data buatan.
2. Integritas data tetap terjaga dengan *Foreign Key* ke tabel Desa.
3. Kinerja kueri agregasi per desa dan kecamatan menjadi lebih cepat karena terhubung langsung ke ID desa.
4. Laporan capaian target desa dan insentif tetap transparan dan adil.
