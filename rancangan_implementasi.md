# Rancangan Implementasi: Sistem Pembagian Upah Kerja Kolektor Berbasis Rentang Tanggal Dinamis (Dynamic Date-Range Payout & Carry-Over $\le 10$ OP)

**Sistem Informasi Pengelolaan Realisasi & Target PBB-P2**  
*Dokumen Rencana Teknis, Analisis Aturan Bisnis, dan Arsitektur Database & Aplikasi*  
*Pembaruan: 24 September 2026*

---

## 1. Latar Belakang & Analisis Kebutuhan Bisnis

Dalam pengelolaan Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2), petugas kolektor desa/kelurahan menerima imbalan atas jasa penagihan yang disebut **Upah Kerja** (atau Biaya Pungut). Upah kerja ini dihitung berdasarkan **Jumlah Objek Pajak (OP)** yang berhasil ditagih dan disetorkan ke kas daerah.

Berdasarkan kondisi nyata tata kelola keuangan daerah:
1. **Pencairan Bertahap Berbasis Ketersediaan Anggaran (Rentang Tanggal Dinamis)**:
   - Pencairan upah kerja dilakukan bertahap dalam setahun, namun **TIDAK TERIKAT pada siklus kaku** seperti triwulan (3 bulan) atau caturwulan (4 bulan).
   - Waktu dan frekuensi pencairan disesuaikan sepenuhnya dengan **ketersediaan anggaran kas daerah** (likuiditas kasda / terbitnya SP2D GU/TU/LS).
   - Oleh karena itu, setiap kali pembayaran upah kerja akan diproses, bendahara/admin menentukan **rentang tanggal bayar secara dinamis**, yaitu:
     $$\text{Tanggal Bayar Awal (tgl\_awal)} \quad \text{s.d.} \quad \text{Tanggal Bayar Akhir (tgl\_akhir)}$$
     *Contoh skenario riil:*
     - Pembayaran Tahap 1: Tanggal `01-01-2026` s.d. `25-03-2026` (sesuai anggaran awal tahun).
     - Pembayaran Tahap 2: Tanggal `26-03-2026` s.d. `10-07-2026` (saat anggaran tahap berikutnya tersedia).
     - Pembayaran Tahap 3: Tanggal `11-07-2026` s.d. `15-10-2026`.
     - Pembayaran Tahap 4 / Terakhir: Tanggal `16-10-2026` s.d. `31-12-2026` (tutup tahun anggaran).

2. **Aturan Ambang Batas Minimal Cair ($\le 10$ OP Carry-Over)**:
   - Untuk efisiensi administrasi perbankan dan pencairan belanja, ditetapkan ambang batas (*threshold*):
     Jika pada rentang tanggal yang diproses seorang kolektor mengumpulkan total realisasi **$\le 10$ OP (10 ke bawah)**:
     - Upah kerja kolektor tersebut **TIDAK DIBAYARKAN** pada pembayaran tahap tersebut.
     - Hak OP tersebut **DITUNDA dan DIBAWAKAN / DIAKUMULASIKAN (*carry-over*)** ke rentang tanggal pembayaran berikutnya.
     - Pada pembayaran tahap berikutnya, OP bawaan tersebut dijumlahkan dengan OP baru yang disetor pada rentang tanggal berikutnya. Jika akumulasinya sudah $> 10$ OP, maka seluruh akumulasi OP tersebut langsung dicairkan.

3. **Pengecualian Khusus Pembayaran Terakhir (Bulan Desember / Tutup Tahun Anggaran)**:
   - Pada pembayaran tahap terakhir tahun anggaran berjalan (bulan Desember):
     **SELURUH SISA HAK UPAH KERJA KOLEKTOR HARUS DIBAYARKAN HABIS (100%)**, meskipun realisasi OP kolektor tersebut $\le 10$ OP (misalnya hanya tersisa 1, 3, atau 8 OP).
   - *Alasan hukum/keuangan:* Aturan pengelolaan keuangan daerah melarang adanya utang belanja upah kerja yang terbawa ke tahun anggaran berikutnya (*zero-carryover at year-end*).

---

## 2. Tantangan Arsitektur Teknis (Technical Challenge)

Jika rentang tanggal bayar bersifat bebas dan dinamis (tidak terjadwal kaku):
1. **Masalah Pelacakan Status Hak OP (*Unpaid vs Paid Tracking*)**:
   - Jika sistem hanya mengandalkan query `SUM(jml_op)` pada tabel transaksi `trn_realisasi_dsh` berdasarkan `tgl_bayar BETWEEN tgl_awal AND tgl_akhir`:
     - Sistem **tidak mengetahui** transaksi setoran mana yang sebenarnya sudah dicairkan pada tahap pembayaran sebelumnya dan mana yang tertunda karena $\le 10$ OP.
   - *Contoh Kasus:*
     - Pada Tahap 1 (`01-01-2026` s.d. `25-03-2026`), Kolektor B mengumpulkan 50 OP ($> 10$, sudah dibayarkan), sedangkan Kolektor A mengumpulkan 6 OP ($\le 10$, ditunda).
     - Pada Tahap 2 (`26-03-2026` s.d. `10-07-2026`), jika sistem hanya melihat transaksi tanggal 26 Maret s.d. 10 Juli:
       - 6 OP milik Kolektor A dari Tahap 1 akan **hilang/tidak terbayar**, karena tanggal setorannya (misal 15 Februari) berada di luar rentang Tahap 2.
       - Jika admin memperluas filter menjadi `01-01-2026` s.d. `10-07-2026`, maka 50 OP milik Kolektor B yang sudah dibayar di Tahap 1 akan **terbayar dua kali (double payout)**.
2. **Kesimpulan Arsitektur**:
   Sistem wajib memiliki mekanisme **Pencatatan Riwayat Pembayaran Bertahap (*Payment Run / Batch Settlement*)** atau pelacakan status pembayaran per transaksi setoran, sehingga sistem secara otomatis mengetahui saldo OP bawaan (*carry-over*) setiap kolektor yang belum terbayar saat rentang tanggal baru dipilih.

---

## 3. Formulasi Logika Bisnis & Perhitungan Matematis

### 3.1 Parameter & Rumus Perhitungan

Untuk setiap kolektor $i$ pada suatu proses pembayaran tahap $t$:

$$\text{Input: } [\text{tgl\_awal}, \text{tgl\_akhir}], \quad \text{IsFinal} \in \{\text{True}, \text{False}\}$$

1. **$\text{OP\_Baru}_{i}$ (Realisasi Periode Berjalan)**:  
   Total lembar OP yang disetorkan oleh kolektor $i$ dengan tanggal bayar pada rentang:
   $$\text{tgl\_bayar} \ge \text{tgl\_awal} \quad \text{AND} \quad \text{tgl\_bayar} \le \text{tgl\_akhir}$$

2. **$\text{OP\_Bawaan}_{i}$ (Carry-Over Tertunda dari Pembayaran Sebelumnya)**:  
   Total lembar OP milik kolektor $i$ dari pembayaran tahap-tahap terdahulu dalam tahun yang sama yang **belum pernah dibayarkan** karena akumulasi sebelumnya $\le 10$ OP.
   *(Jika ini adalah pembayaran pertama dalam tahun anggaran, maka $\text{OP\_Bawaan}_{i} = 0$)*.

3. **$\text{OP\_Akumulasi}_{i}$ (Total Hak OP Saat Ini)**:
   $$\text{OP\_Akumulasi}_{i} = \text{OP\_Bawaan}_{i} + \text{OP\_Baru}_{i}$$

4. **Penentuan Status Kelayakan Bayar & Jumlah OP Cair**:

   - **Kasus A: Pembayaran Terakhir (Bulan Desember / Tutup Tahun / $\text{IsFinal} = \text{True}$)**:
     Semua hak OP wajib dibayarkan tanpa syarat minimal:
     $$\begin{cases}
     \text{Status} = \text{"DIBAYARKAN (PELUNASAN AKHIR TAHUN)"} \\
     \text{OP\_Dibayarkan}_{i} = \text{OP\_Akumulasi}_{i} \\
     \text{Sisa\_OP\_Ditunda}_{i} = 0
     \end{cases}$$

   - **Kasus B: Pembayaran Reguler (Bukan Pembayaran Terakhir / $\text{IsFinal} = \text{False}$)**:
     - **Sub-Kasus B1: Jika $\text{OP\_Akumulasi}_{i} > 10$ OP**:
       $$\begin{cases}
       \text{Status} = \text{"DIBAYARKAN"} \\
       \text{OP\_Dibayarkan}_{i} = \text{OP\_Akumulasi}_{i} \\
       \text{Sisa\_OP\_Ditunda}_{i} = 0
       \end{cases}$$
     - **Sub-Kasus B2: Jika $\text{OP\_Akumulasi}_{i} \le 10$ OP**:
       $$\begin{cases}
       \text{Status} = \text{"DITUNDA (CARRY-OVER)"} \\
       \text{OP\_Dibayarkan}_{i} = 0 \\
       \text{Sisa\_OP\_Ditunda}_{i} = \text{OP\_Akumulasi}_{i}
       \end{cases}$$

---

### 3.2 Simulasi Siklus Kasus Nyata

Berikut simulasi 3 tahap pembayaran dengan tanggal yang disesuaikan ketersediaan kas:

| Kolektor | Tahap 1: `01/01` s.d. `20/03`<br>*(Bukan Terakhir)* | Tahap 2: `21/03` s.d. `15/07`<br>*(Bukan Terakhir)* | Tahap 3 / Final: `16/07` s.d. `31/12`<br>*(Pembayaran Terakhir / Desember)* |
| :--- | :--- | :--- | :--- |
| **Kolektor A** | • OP Baru: **7 OP**<br>• Total: **7 OP** ($\le 10$)<br>$\rightarrow$ **DITUNDA (0 OP Cair)**<br>• Sisa Carry: 7 OP | • Bawaan: 7 OP<br>• OP Baru: **5 OP**<br>• Total: **12 OP** ($> 10$)<br>$\rightarrow$ **DIBAYARKAN (12 OP Cair)**<br>• Sisa Carry: 0 OP | • Bawaan: 0 OP<br>• OP Baru: **4 OP**<br>• Total: **4 OP**<br>$\rightarrow$ **DIBAYARKAN (4 OP Cair - Final)**<br>• Sisa Carry: 0 OP |
| **Kolektor B** | • OP Baru: **35 OP**<br>• Total: **35 OP** ($> 10$)<br>$\rightarrow$ **DIBAYARKAN (35 OP Cair)**<br>• Sisa Carry: 0 OP | • Bawaan: 0 OP<br>• OP Baru: **4 OP**<br>• Total: **4 OP** ($\le 10$)<br>$\rightarrow$ **DITUNDA (0 OP Cair)**<br>• Sisa Carry: 4 OP | • Bawaan: 4 OP<br>• OP Baru: **8 OP**<br>• Total: **12 OP**<br>$\rightarrow$ **DIBAYARKAN (12 OP Cair - Final)**<br>• Sisa Carry: 0 OP |
| **Kolektor C** | • OP Baru: **2 OP**<br>• Total: **2 OP** ($\le 10$)<br>$\rightarrow$ **DITUNDA (0 OP Cair)**<br>• Sisa Carry: 2 OP | • Bawaan: 2 OP<br>• OP Baru: **3 OP**<br>• Total: **5 OP** ($\le 10$)<br>$\rightarrow$ **DITUNDA (0 OP Cair)**<br>• Sisa Carry: 5 OP | • Bawaan: 5 OP<br>• OP Baru: **2 OP**<br>• Total: **7 OP** ($\le 10$)<br>$\rightarrow$ **DIBAYARKAN (7 OP Cair - Final)**<br>• Sisa Carry: 0 OP |

> [!IMPORTANT]
> Perhatikan **Kolektor C**: Meskipun di Tahap 1, 2, dan 3 realisasinya selalu kecil ($\le 10$ OP) dan di tahap akhir total akumulasinya hanya 7 OP, pada **Tahap 3 (Pembayaran Terakhir / Desember) seluruh 7 OP tersebut wajib dibayarkan 100%**, sehingga hak kolektor tidak hangus dan buku kas pemda tutup tanpa utang.

---

## 4. Rincian Teknis Perbaikan Arsitektur & Database

Untuk mendukung rentang tanggal bayar yang bebas dinamis sekaligus mencatat carry-over tanpa cacat integritas, dirancang struktur tabel yang ringan namun kokoh:

### 4.1 Tabel Riwayat Pembayaran Bertahap (`trn_upah_kerja_tahap`)
Tabel ini mencatat setiap kali bendahara memproses pencairan upah kerja berdasarkan rentang tanggal bayar yang dipilih:

```sql
CREATE TABLE `trn_upah_kerja_tahap` (
  `tahap_id`        int NOT NULL AUTO_INCREMENT,
  `tahun`           int(4) NOT NULL DEFAULT '2026',
  `nama_tahap`      varchar(128) NOT NULL,            -- Contoh: 'Pencairan Tahap 1 (Kasda Triwulan I)'
  `tgl_awal`        date NOT NULL,                    -- Tanggal bayar awal yang dipilih
  `tgl_akhir`       date NOT NULL,                    -- Tanggal bayar akhir yang dipilih
  `is_final`        tinyint(1) NOT NULL DEFAULT '0',  -- 1 jika dicentang 'Pembayaran Terakhir / Desember'
  `total_kolektor`  int NOT NULL DEFAULT '0',
  `total_op_cair`   int NOT NULL DEFAULT '0',
  `total_op_tunda`  int NOT NULL DEFAULT '0',
  `created_by`      varchar(64) DEFAULT NULL,
  `created_at`      datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tahap_id`),
  INDEX `idx_tahap_tahun` (`tahun`, `tgl_awal`, `tgl_akhir`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4.2 Tabel Rincian Realisasi Upah Kerja Kolektor (`trn_upah_kerja_detail`)
Menyimpan rincian carry-over masuk, realisasi baru pada rentang tanggal tersebut, status kelayakan, dan sisa carry-over keluar per kolektor:

```sql
CREATE TABLE `trn_upah_kerja_detail` (
  `detail_id`           int NOT NULL AUTO_INCREMENT,
  `tahap_id`            int NOT NULL,
  `kolektor_id`         int NOT NULL,
  `op_carry_masuk`      int NOT NULL DEFAULT '0',     -- OP tertunda dari tahap pembayaran sebelumnya
  `op_periode_ini`      int NOT NULL DEFAULT '0',     -- Realisasi OP setoran pada [tgl_awal s.d. tgl_akhir]
  `op_total_akumulasi`  int NOT NULL DEFAULT '0',     -- op_carry_masuk + op_periode_ini
  `status_bayar`        enum('DIBAYARKAN','DITUNDA') NOT NULL DEFAULT 'DITUNDA',
  `op_dibayarkan`       int NOT NULL DEFAULT '0',     -- Nilai OP yang cair pada tahap ini
  `op_carry_keluar`     int NOT NULL DEFAULT '0',     -- Sisa OP tertunda yang dibawa ke tahap berikutnya
  PRIMARY KEY (`detail_id`),
  UNIQUE KEY `uk_tahap_kolektor` (`tahap_id`, `kolektor_id`),
  CONSTRAINT `fk_detail_tahap` FOREIGN KEY (`tahap_id`) REFERENCES `trn_upah_kerja_tahap` (`tahap_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_kolektor` FOREIGN KEY (`kolektor_id`) REFERENCES `mst_kolektor` (`kolektor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 5. Rincian Modifikasi Antarmuka Pengguna (UI/UX)

Halaman **Laporan Upah Kerja** ([`app/Views/admin/upah_kerja.php`](file:///var/www/html/pbb_ar/app/Views/admin/upah_kerja.php)) disempurnakan sebagai berikut:

### 5.1 Panel Filter Interaktif
1. **Filter Rentang Tanggal Bayar Fleksibel**:
   - `Tanggal Bayar Awal (tgl_awal)`: input tipe tanggal (contoh: `2026-01-01`).
   - `Tanggal Bayar Akhir (tgl_akhir)`: input tipe tanggal (contoh: `2026-03-25`).
   - Sesuai dengan instruksi pengguna, rentang tanggal ini bebas dipilih sesuai ketersediaan kas daerah.
2. **Sakelar / Checkbox "Pembayaran Terakhir (Bulan Desember / Tutup Tahun)"**:
   - `[✓] Pembayaran Terakhir (Pelunasan Akhir Tahun / Desember - Bebas Batas Minimal 10 OP)`
   - Bila dicentang:
     - Tampil banner informatif berwarna ungu/biru:  
       *“Mode Pelunasan Akhir Tahun Aktif: Seluruh kolektor berapapun jumlah OP-nya ($\le 10$ OP) akan dibayarkan penuh.”*
3. **Filter Pendukung**:
   - Filter Kecamatan & Desa (dengan dynamic AJAX cascade).
   - Filter Status Kelayakan: `Semua Status`, `Hanya yang Siap Dibayarkan`, `Hanya yang Ditunda (<= 10 OP)`.
   - Pencarian Nama Kolektor.

### 5.2 Modifikasi Kolom Tabel Data
Tabel Upah Kerja yang sebelumnya hanya 5 kolom disempurnakan menjadi **10 kolom informatif**:

| No | Nama Kecamatan | Nama Desa | Nama Kolektor | No. Rekening Kolektor | OP Bawaan Lalu | OP Periode Ini | Total Akumulasi OP | Status Pembayaran | OP Dibayarkan |
| :---: | :--- | :--- | :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| 1 | SINJAI BARAT | ARABIKA | UMAR C. | 0123456789 | 6 | 8 | 14 | <span style="color:green;font-weight:bold">DIBAYARKAN</span> | **14 OP** |
| 2 | SINJAI BARAT | ARABIKA | FARIDA | 9876543210 | 0 | 4 | 4 | <span style="color:orange;font-weight:bold">DITUNDA (&le; 10 OP)</span> | **0 OP** *(sisa 4)* |

### 5.3 Kartu Ringkasan (Summary Cards)
Pada bagian atas tabel, disajikan 4 kartu ringkasan real-time:
1. **Total Kolektor Ber-OP**: Jumlah kolektor yang memiliki setoran/hak upah pada tahap ini.
2. **Kolektor Siap Cair**: Jumlah kolektor yang memenuhi syarat pencairan ($> 10$ OP atau mode akhir tahun).
3. **Total OP Dicairkan**: Total fisik lembar OP yang resmi dibayarkan pada pembayaran ini.
4. **Total OP Ditunda (*Carry-Over*)**: Total OP yang ditangguhkan ke pembayaran tahap berikutnya karena $\le 10$ OP.

### 5.4 Tombol Aksi Tambahan
- **Tombol "Export Excel (.xls)"**: Menghasilkan file Excel resmi lengkap dengan kolom carry-over dan status pembayaran.
- **Tombol "Simpan / Kunci Pembayaran Tahap Ini"**:
  Menyimpan hasil perhitungan rentang tanggal yang dipilih ke dalam tabel database `trn_upah_kerja_tahap`, sehingga ketika bendahara nantinya membuka rentang tanggal berikutnya, sistem secara otomatis mengenali saldo carry-over yang belum cair.

---

## 6. Algoritma Controller (`app/Controllers/Admin.php`)

Logika perhitungan di method `upahKerja()` dan `exportUpahKerja()` diperbarui dengan algoritma berikut:

```php
// 1. Ambil Parameter Filter
$tahun        = (int)($this->request->getGet('tahun') ?? 2026);
$tglAwal      = $this->request->getGet('tgl_awal');
$tglAkhir     = $this->request->getGet('tgl_akhir');
$isFinalMonth = (bool)$this->request->getGet('is_final'); // Checkbox 'Pembayaran Terakhir / Desember'
$minThreshold = 10; // Ambang batas 10 OP

// 2. Identifikasi Saldo Carry-Over Masuk (op_carry_masuk)
// Mengambil sisa OP kolektor dari tahap pembayaran terakhir yang sudah tercatat sebelum tglAwal,
// atau menghitung saldo belum terbayar dari transaksi terdahulu dalam tahun yang sama.
$carryOverMap = $this->getCollectorCarryOverBalances($tahun, $tglAwal);

// 3. Query Realisasi Baru pada Rentang Tanggal Bayar Terpilih
$builder = $this->db->table('mst_kolektor c')
    ->select('
        k.nm_kecamatan, d.nm_desa, c.kolektor_id, c.kd_kolektor, c.nm_kolektor, c.norek_kolektor,
        COALESCE(SUM(r.jml_op), 0) AS op_periode_ini
    ')
    ->join('mst_desa d', 'c.desa_id = d.desa_id')
    ->join('mst_kecamatan k', 'd.kecamatan_id = k.kecamatan_id')
    ->join('trn_realisasi_dsh r', "r.kolektor_id = c.kolektor_id AND r.tahun = c.tahun AND r.tgl_bayar >= '$tglAwal' AND r.tgl_bayar <= '$tglAkhir'", 'left')
    ->where('c.tahun', $tahun)
    ->groupBy('c.kolektor_id, k.nm_kecamatan, d.nm_desa, c.kd_kolektor, c.nm_kolektor, c.norek_kolektor');

$kolektors = $builder->get()->getResultArray();

// 4. Kalkulasi Status & Carry-Over Per Kolektor
$dataReport = [];
$totalOpCair = 0;
$totalOpTunda = 0;

foreach ($kolektors as $col) {
    $colId = $col['kolektor_id'];
    $opCarryMasuk = $carryOverMap[$colId] ?? 0;
    $opPeriodeIni = (int)$col['op_periode_ini'];
    $opTotalAkumulasi = $opCarryMasuk + $opPeriodeIni;

    // Evaluasi aturan pembayaran
    if ($isFinalMonth) {
        // Pembayaran Terakhir (Desember): Semua dibayarkan 100%
        $statusBayar = ($opTotalAkumulasi > 0) ? 'DIBAYARKAN' : 'TIDAK_ADA_OP';
        $opDibayarkan = $opTotalAkumulasi;
        $opCarryKeluar = 0;
    } else {
        // Pembayaran Reguler bertahap: Batas 10 OP
        if ($opTotalAkumulasi > $minThreshold) {
            $statusBayar = 'DIBAYARKAN';
            $opDibayarkan = $opTotalAkumulasi;
            $opCarryKeluar = 0;
        } else {
            $statusBayar = ($opTotalAkumulasi > 0) ? 'DITUNDA' : 'TIDAK_ADA_OP';
            $opDibayarkan = 0;
            $opCarryKeluar = $opTotalAkumulasi; // Ditunda ke tahap berikutnya
        }
    }

    $col['op_carry_masuk']      = $opCarryMasuk;
    $col['op_periode_ini']      = $opPeriodeIni;
    $col['op_total_akumulasi']  = $opTotalAkumulasi;
    $col['status_bayar']        = $statusBayar;
    $col['op_dibayarkan']       = $opDibayarkan;
    $col['op_carry_keluar']     = $opCarryKeluar;

    $totalOpCair  += $opDibayarkan;
    $totalOpTunda += $opCarryKeluar;
    $dataReport[] = $col;
}
```

---

## 7. Rincian Peningkatan Export Excel (.xls)

File hasil unduhan Excel menyajikan informasi yang lengkap dan akuntabel bagi pihak perbankan maupun bendahara:
1. **Header Metadata**:
   - `Tahun Anggaran`: 2026
   - `Rentang Tanggal Bayar`: `dd/mm/yyyy s.d. dd/mm/yyyy` (sesuai pilihan dinamis)
   - `Kategori Pembayaran`: `Tahap Reguler (Ambang Batas > 10 OP)` ATAU `Tahap Terakhir / Pelunasan Akhir Tahun (Bulan Desember)`
   - `Waktu Unduh`: Tanggal & Jam cetak
2. **Kolom Lembar Kerja**:
   - `No`
   - `Kecamatan`
   - `Desa`
   - `Nama Kolektor`
   - `No. Rekening Kolektor` (format teks agar angka 0 di depan tetap utuh)
   - `OP Bawaan Lalu`
   - `OP Periode Ini`
   - `Total Akumulasi OP`
   - `Status Kelayakan` (*DIBAYARKAN* / *DITUNDA*)
   - `Jumlah OP Dibayarkan`
   - `Sisa OP Ditunda`
3. **Baris Total Akumulatif**:
   - Rincian Total OP Dicairkan.
   - Rincian Total OP yang Ditunda ke Periode Berikutnya.

---

## 8. Kesimpulan & Manfaat Implementasi

Dengan penyesuaian rancangan berbasis **Rentang Tanggal Bayar Dinamis** ini:
1. **Fleksibel Mengikuti Anggaran Daerah**: Bendahara tidak lagi dipaksa menggunakan pola kuartalan/caturwulanan, melainkan bebas menentukan periode bayar kapan pun kas daerah siap.
2. **Keadilan bagi Kolektor Terjamin**: Tidak ada hak upah kolektor yang hilang karena OP $\le 10$ akan otomatis terbawa (*carry-over*) hingga mencapai $> 10$ OP.
3. **Tertib Administrasi Akhir Tahun**: Pada bulan Desember, penanda *Pembayaran Terakhir* memastikan tidak ada sisa utang upah pemungutan yang melompat ke tahun anggaran berikutnya.
4. **Data Transparan & Siap Audit**: Dokumen laporan dan Excel membedakan dengan jelas antara setoran periode berjalan, carry-over masuk, upah yang cair, dan carry-over keluar.
