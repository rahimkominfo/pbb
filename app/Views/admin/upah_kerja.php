<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Laporan Upah Kerja - PBB-AR Sinjai<?= $this->endSection() ?>

<?= $this->section('page_header') ?>Laporan Upah Kerja Kolektor<?= $this->endSection() ?>

<?= $this->section('header_actions') ?>
<?php 
$exportUrl = base_url('admin/upah-kerja/export?' . http_build_query([
    'tahun'        => $selectedYear,
    'tgl_awal'     => $tglAwal,
    'tgl_akhir'    => $tglAkhir,
    'is_final'     => $isFinal ? 1 : 0,
    'kecamatan_id' => $selectedKec,
    'desa_id'      => $selectedDesa,
    'search'       => $search,
    'status_bayar' => $statusBayar,
]));
?>
<div class="flex items-center gap-2">
    <?php if (!empty($tglAwal) && !empty($tglAkhir)): ?>
        <button type="button" onclick="openSimpanTahapModal()" 
                class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center gap-1.5 shadow-md shadow-indigo-500/20">
            <i class="fa-solid fa-lock"></i>
            <span>Kunci & Simpan Tahap</span>
        </button>
    <?php endif; ?>

    <a href="<?= $exportUrl ?>" 
       class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center gap-1.5 shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/25">
        <i class="fa-solid fa-file-excel"></i>
        <span>Export Excel</span>
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan Upah Kerja Kolektor</h1>
            <p class="text-xs text-slate-400 mt-1">
                Pencairan upah kerja bertahap berbasis rentang tanggal bayar dinamis, aturan carry-over (&le; 10 OP), dan pelunasan akhir tahun.
            </p>
        </div>
    </div>

    <!-- Riwayat Tahap Pembayaran yang Sudah Disimpan (Jika ada) -->
    <?php if (!empty($tahapList)): ?>
        <div class="glass-panel rounded-3xl p-5 shadow-lg border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-400"></i>
                    <span>Riwayat Tahap Pembayaran Terkunci (Tahun <?= $selectedYear ?>)</span>
                </span>
                <span class="text-[11px] text-slate-500">Klik tahap untuk memuat data</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php foreach ($tahapList as $thp): 
                    $isActive = ($tglAwal === $thp['tgl_awal'] && $tglAkhir === $thp['tgl_akhir']);
                ?>
                    <div class="p-3.5 rounded-2xl bg-slate-900/80 border <?= $isActive ? 'border-indigo-500 bg-indigo-500/10' : 'border-slate-800' ?> flex items-center justify-between gap-3 transition-all hover:border-slate-700">
                        <a href="<?= base_url('admin/upah-kerja?tahun=' . $selectedYear . '&tgl_awal=' . $thp['tgl_awal'] . '&tgl_akhir=' . $thp['tgl_akhir'] . ($thp['is_final'] ? '&is_final=1' : '')) ?>" 
                           class="flex-grow min-w-0 group">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-white group-hover:text-indigo-400 truncate"><?= esc($thp['nama_tahap']) ?></span>
                                <?php if ($thp['is_final']): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-rose-500/20 text-rose-400 border border-rose-500/30">FINAL</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                <?= date('d/m/Y', strtotime($thp['tgl_awal'])) ?> s.d. <?= date('d/m/Y', strtotime($thp['tgl_akhir'])) ?>
                            </div>
                            <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-2">
                                <span class="text-emerald-400 font-semibold"><?= number_format($thp['total_op_cair'], 0, ',', '.') ?> OP Cair</span>
                                <span>•</span>
                                <span class="text-amber-400 font-semibold"><?= number_format($thp['total_op_tunda'], 0, ',', '.') ?> OP Ditunda</span>
                            </div>
                        </a>
                        <form action="<?= base_url('admin/upah-kerja/hapus-tahap') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus tahap ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="tahap_id" value="<?= $thp['tahap_id'] ?>">
                            <button type="submit" class="h-7 w-7 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 flex items-center justify-center transition-all" title="Hapus tahap ini">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filter Panel -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg">
        <form method="GET" action="<?= base_url('admin/upah-kerja') ?>" id="filter-form" class="space-y-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Filter Tahun -->
                <div class="space-y-1.5">
                    <label for="tahun" class="text-xs font-semibold text-slate-300">Tahun Pajak</label>
                    <select id="tahun" name="tahun" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer font-bold">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= (int)$selectedYear === (int)$y ? 'selected' : '' ?>>Tahun <?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Tanggal Bayar Awal -->
                <div class="space-y-1.5">
                    <label for="tgl_awal" class="text-xs font-semibold text-slate-300">Tanggal Bayar Awal</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500 pointer-events-none">
                            <i class="fa-solid fa-calendar-day text-xs"></i>
                        </span>
                        <input type="date" id="tgl_awal" name="tgl_awal" value="<?= esc($tglAwal) ?>"
                               class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-10 pr-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    </div>
                </div>

                <!-- Filter Tanggal Bayar Akhir -->
                <div class="space-y-1.5">
                    <label for="tgl_akhir" class="text-xs font-semibold text-slate-300">Tanggal Bayar Akhir</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500 pointer-events-none">
                            <i class="fa-solid fa-calendar-day text-xs"></i>
                        </span>
                        <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?= esc($tglAkhir) ?>"
                               class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-10 pr-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    </div>
                </div>

                <!-- Filter Status Kelayakan Bayar -->
                <div class="space-y-1.5">
                    <label for="status_bayar" class="text-xs font-semibold text-slate-300">Status Pembayaran</label>
                    <select id="status_bayar" name="status_bayar" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="all" <?= $statusBayar === 'all' ? 'selected' : '' ?>>Semua Kolektor</option>
                        <option value="has_op" <?= $statusBayar === 'has_op' ? 'selected' : '' ?>>Semua yang Ada OP (&gt; 0)</option>
                        <option value="dibayarkan" <?= $statusBayar === 'dibayarkan' ? 'selected' : '' ?>>Siap Dibayarkan (&gt; 10 OP / Final)</option>
                        <option value="ditunda" <?= $statusBayar === 'ditunda' ? 'selected' : '' ?>>Ditunda ke Tahap Depan (&le; 10 OP)</option>
                    </select>
                </div>
            </div>

            <!-- Checkbox Mode Pelunasan Akhir Tahun (Desember) -->
            <div class="p-3.5 rounded-2xl bg-slate-900/60 border <?= $isFinal ? 'border-amber-500/50 bg-amber-500/10' : 'border-slate-800' ?> transition-colors">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_final" value="1" <?= $isFinal ? 'checked' : '' ?> 
                           onchange="document.getElementById('filter-form').submit()"
                           class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900 cursor-pointer">
                    <div>
                        <span class="text-xs font-bold <?= $isFinal ? 'text-amber-300' : 'text-slate-200' ?> block">
                            Pembayaran Terakhir / Pelunasan Akhir Tahun (Bulan Desember)
                        </span>
                        <span class="text-[11px] text-slate-400 block mt-0.5">
                            Centang opsi ini jika merupakan pembayaran penutup tahun. <strong>Seluruh hak OP kolektor (termasuk yang &le; 10 OP) wajib dibayarkan 100%</strong> tanpa ditunda.
                        </span>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                <!-- Filter Kecamatan -->
                <div class="space-y-1.5">
                    <label for="kecamatan_id" class="text-xs font-semibold text-slate-300">Kecamatan</label>
                    <select id="kecamatan_id" name="kecamatan_id" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php foreach ($kecamatans as $kec): ?>
                            <option value="<?= $kec['kecamatan_id'] ?>" <?= (string)$selectedKec === (string)$kec['kecamatan_id'] ? 'selected' : '' ?>>
                                <?= esc($kec['nm_kecamatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Desa -->
                <div class="space-y-1.5">
                    <label for="desa_id" class="text-xs font-semibold text-slate-300">Desa / Kelurahan</label>
                    <select id="desa_id" name="desa_id" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer" <?= empty($selectedKec) ? 'disabled' : '' ?>>
                        <option value="">-- Semua Desa --</option>
                        <?php if (!empty($selectedKec)): ?>
                            <?php foreach ($desas as $d): ?>
                                <option value="<?= $d['desa_id'] ?>" <?= (string)$selectedDesa === (string)$d['desa_id'] ? 'selected' : '' ?>>
                                    <?= esc($d['nm_desa']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Cari Nama Kolektor -->
                <div class="space-y-1.5">
                    <label for="search" class="text-xs font-semibold text-slate-300">Cari Nama Kolektor</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" id="search" name="search" value="<?= esc($search) ?>" placeholder="Nama kolektor..." 
                               class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-10 pr-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    </div>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-800/80">
                <div class="flex items-center gap-2">
                    <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-500/20 active:scale-[0.98] transition-all cursor-pointer flex items-center gap-2">
                        <i class="fa-solid fa-filter"></i>
                        <span>Terapkan Filter</span>
                    </button>
                    
                    <?php if (!empty($tglAwal) || !empty($tglAkhir) || !empty($selectedKec) || !empty($selectedDesa) || !empty($search) || $statusBayar !== 'all' || $isFinal): ?>
                        <a href="<?= base_url('admin/upah-kerja?tahun=' . $selectedYear) ?>" class="py-2.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-semibold transition-all flex items-center gap-1.5" title="Reset filter">
                            <i class="fa-solid fa-rotate-left"></i>
                            <span>Reset</span>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-2">
                    <?php if (!empty($tglAwal) && !empty($tglAkhir)): ?>
                        <button type="button" onclick="openSimpanTahapModal()" 
                                class="py-2.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-500/20 active:scale-[0.98] transition-all cursor-pointer flex items-center gap-2">
                            <i class="fa-solid fa-lock"></i>
                            <span>Simpan Tahap Ini</span>
                        </button>
                    <?php endif; ?>

                    <a href="<?= $exportUrl ?>" 
                       class="py-2.5 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all cursor-pointer flex items-center gap-2">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Export Excel (.xls)</span>
                    </a>
                </div>
            </div>

        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Kolektor Ber-OP Card -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-indigo-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kolektor Ber-OP</span>
                <div class="h-7 w-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-users text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-xl font-extrabold text-white"><?= number_format($totalKolektorBerOp, 0, ',', '.') ?></span>
                <span class="text-[10px] text-slate-500 block mt-0.5">Memiliki Hak OP di Periode Ini</span>
            </div>
        </div>

        <!-- Kolektor Siap Cair Card -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-emerald-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kolektor Siap Cair</span>
                <div class="h-7 w-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-user-check text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-xl font-extrabold text-emerald-400"><?= number_format($totalKolektorSiapCair, 0, ',', '.') ?></span>
                <span class="text-[10px] text-slate-500 block mt-0.5">
                    <?= $isFinal ? 'Pelunasan 100% (Desember)' : '&gt; 10 OP (Syarat Terpenuhi)' ?>
                </span>
            </div>
        </div>

        <!-- Total OP Dicairkan Card -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-teal-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total OP Dicairkan</span>
                <div class="h-7 w-7 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-xl font-extrabold text-teal-400"><?= number_format($totalOpCair, 0, ',', '.') ?> OP</span>
                <span class="text-[10px] text-slate-500 block mt-0.5">Hak Cair Pembayaran Ini</span>
            </div>
        </div>

        <!-- Total OP Ditunda (Carry-Over) Card -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-amber-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">OP Ditunda (Carry-Over)</span>
                <div class="h-7 w-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-xl font-extrabold text-amber-400"><?= number_format($totalOpTunda, 0, ',', '.') ?> OP</span>
                <span class="text-[10px] text-slate-500 block mt-0.5">
                    <?= $isFinal ? '0 OP (Semua Lunas)' : 'Diakumulasikan ke Tahap Depan (&le; 10 OP)' ?>
                </span>
            </div>
        </div>

    </div>

    <!-- Table Section -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-800/80 pb-4 gap-3">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-indigo-400"></i>
                    <span>Tabel Rincian Upah Kerja Kolektor</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <span class="text-indigo-400 font-bold"><?= count($dataReport) ?></span> data kolektor
                    <?php if (!empty($tglAwal) || !empty($tglAkhir)): ?>
                        • Rentang: <span class="text-slate-300 font-semibold"><?= !empty($tglAwal) ? date('d/m/Y', strtotime($tglAwal)) : 'Awal Tahun' ?> s.d. <?= !empty($tglAkhir) ? date('d/m/Y', strtotime($tglAkhir)) : 'Sekarang' ?></span>
                    <?php endif; ?>
                    <?php if ($isFinal): ?>
                        • <span class="text-amber-400 font-bold"><i class="fa-solid fa-circle-info"></i> Mode Pelunasan Desember Aktif</span>
                    <?php endif; ?>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= $exportUrl ?>" 
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center gap-1.5 shadow-md shadow-emerald-500/10">
                    <i class="fa-solid fa-file-excel"></i> Export Excel (.xls)
                </a>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm border-collapse min-w-[1000px]">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Kecamatan</th>
                        <th class="py-3.5 px-4">Nama Desa</th>
                        <th class="py-3.5 px-4">Nama Kolektor</th>
                        <th class="py-3.5 px-4">No. Rekening Kolektor</th>
                        <th class="py-3.5 px-4 text-right">OP Lalu</th>
                        <th class="py-3.5 px-4 text-right">OP Baru</th>
                        <th class="py-3.5 px-4 text-right">Total Akumulasi</th>
                        <th class="py-3.5 px-4 text-center">Status Pembayaran</th>
                        <th class="py-3.5 px-4 text-right">OP Dibayarkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    <?php if (empty($dataReport)): ?>
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation text-3xl text-slate-600"></i>
                                    <span class="text-sm font-medium">Tidak ada data kolektor yang sesuai dengan filter yang dipilih.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($dataReport as $row): 
                            $status = $row['status_bayar'];
                            $opMasuk = (int)$row['op_carry_masuk'];
                            $opIni = (int)$row['op_periode_ini'];
                            $opTotal = (int)$row['op_total_akumulasi'];
                            $opCair = (int)$row['op_dibayarkan'];
                        ?>
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-500 font-medium text-xs">
                                    <?= $no++ ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400 font-semibold text-xs uppercase tracking-wide">
                                    <?= esc($row['nm_kecamatan']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-white font-bold">
                                    <?= esc($row['nm_desa']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            <?= esc($row['kd_kolektor']) ?>
                                        </div>
                                        <span class="text-white font-bold"><?= esc($row['nm_kolektor']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs">
                                    <?php if (!empty($row['norek_kolektor'])): ?>
                                        <span class="text-slate-200 bg-slate-800/80 px-2.5 py-1 rounded-lg border border-slate-700/60 font-semibold tracking-wider">
                                            <?= esc($row['norek_kolektor']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-600 italic">Belum diatur</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-xs text-slate-400">
                                    <?= $opMasuk > 0 ? '<span class="text-amber-400 font-semibold">+' . number_format($opMasuk, 0, ',', '.') . '</span>' : '-' ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-xs text-slate-300">
                                    <?= number_format($opIni, 0, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-xs font-bold text-white">
                                    <?= number_format($opTotal, 0, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($status === 'DIBAYARKAN'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i>
                                            <span><?= $isFinal ? 'Dibayarkan (Final)' : 'Dibayarkan' ?></span>
                                        </span>
                                    <?php elseif ($status === 'DITUNDA'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20" title="Ditunda ke pembayaran berikutnya karena &le; 10 OP">
                                            <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                                            <span>Ditunda (&le; 10 OP)</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-600 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <?php if ($opCair > 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <?= number_format($opCair, 0, ',', '.') ?> OP
                                        </span>
                                    <?php else: ?>
                                        <span class="font-mono text-xs text-slate-600 font-semibold">
                                            0 OP
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($dataReport)): ?>
                    <tfoot>
                        <tr class="border-t-2 border-slate-700/80 bg-slate-900/60 font-bold text-white">
                            <td colspan="5" class="py-4 px-4 text-right text-xs uppercase tracking-wider text-slate-400">
                                Total Akumulasi :
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-xs text-amber-400">
                                <?= number_format($totalOpTunda, 0, ',', '.') ?> Ditunda
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-xs text-slate-400">
                                -
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-xs text-white">
                                <?= number_format($totalOpAkumulasi, 0, ',', '.') ?> OP
                            </td>
                            <td class="py-4 px-4 text-center text-xs uppercase tracking-wider text-slate-400">
                                Total Dicairkan:
                            </td>
                            <td class="py-4 px-4 text-right font-mono text-sm text-emerald-400">
                                <?= number_format($totalOpCair, 0, ',', '.') ?> OP
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

    </div>

</div>

<!-- Modal Kunci / Simpan Tahap Pembayaran -->
<div id="simpan-tahap-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm hidden">
    <div class="glass-panel w-full max-w-lg rounded-3xl p-6 shadow-2xl border border-slate-700 space-y-5 m-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-lock text-indigo-400"></i>
                <span>Kunci & Simpan Tahap Pembayaran</span>
            </h3>
            <button type="button" onclick="closeSimpanTahapModal()" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= base_url('admin/upah-kerja/simpan-tahap') ?>" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="tahun" value="<?= esc($selectedYear) ?>">
            <input type="hidden" name="tgl_awal" value="<?= esc($tglAwal) ?>">
            <input type="hidden" name="tgl_akhir" value="<?= esc($tglAkhir) ?>">
            <input type="hidden" name="is_final" value="<?= $isFinal ? '1' : '0' ?>">

            <div class="p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-slate-300 space-y-1">
                <div>Periode: <strong class="text-white"><?= date('d/m/Y', strtotime($tglAwal)) ?> s.d. <?= date('d/m/Y', strtotime($tglAkhir)) ?></strong></div>
                <div>Status: <strong class="<?= $isFinal ? 'text-rose-400' : 'text-emerald-400' ?>"><?= $isFinal ? 'Pelunasan Akhir Tahun (Desember)' : 'Reguler (Ambang Batas 10 OP)' ?></strong></div>
                <div>Estimasi: <strong class="text-emerald-400"><?= number_format($totalOpCair, 0, ',', '.') ?> OP Cair</strong> (<?= $totalKolektorSiapCair ?> Kolektor) &bull; <strong class="text-amber-400"><?= number_format($totalOpTunda, 0, ',', '.') ?> OP Ditunda</strong></div>
            </div>

            <div class="space-y-1.5">
                <label for="nama_tahap" class="text-xs font-semibold text-slate-300">Nama Tahap Pembayaran</label>
                <input type="text" id="nama_tahap" name="nama_tahap" required 
                       placeholder="Contoh: Pencairan Tahap 1 (Jan - Mar 2026)" 
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <p class="text-[11px] text-slate-400">
                Setelah tahap ini disimpan dan dikunci, kolektor yang memiliki OP ditunda (&le; 10 OP) akan otomatis terbawa (*carry-over*) saat Anda membuat tahap pembayaran berikutnya.
            </p>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="closeSimpanTahapModal()" class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-check"></i>
                    <span>Konfirmasi & Kunci Tahap</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Client-side Script for Dynamic Desa Selection & Modal Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kecSelect = document.getElementById('kecamatan_id');
    const desaSelect = document.getElementById('desa_id');
    const currentDesaId = "<?= esc($selectedDesa) ?>";

    kecSelect.addEventListener('change', async function() {
        const kecId = this.value;
        desaSelect.innerHTML = '<option value="">-- Semua Desa --</option>';

        if (!kecId) {
            desaSelect.disabled = true;
            return;
        }

        desaSelect.disabled = false;
        try {
            const res = await fetch(`<?= base_url('admin/kolektor/get-desas') ?>?kecamatan_id=${kecId}`);
            if (!res.ok) throw new Error();
            const desas = await res.json();
            desas.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.desa_id;
                opt.textContent = d.nm_desa;
                if (String(d.desa_id) === String(currentDesaId)) {
                    opt.selected = true;
                }
                desaSelect.appendChild(opt);
            });
        } catch (e) {
            console.error('Error fetching desas:', e);
        }
    });
});

function openSimpanTahapModal() {
    document.getElementById('simpan-tahap-modal').classList.remove('hidden');
    document.getElementById('nama_tahap').focus();
}

function closeSimpanTahapModal() {
    document.getElementById('simpan-tahap-modal').classList.add('hidden');
}
</script>
<?= $this->endSection() ?>
