<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Laporan Insentif<?= $this->endSection() ?>

<?= $this->section('page_header') ?>Laporan Insentif Realisasi PBB<?= $this->endSection() ?>

<?= $this->section('header_actions') ?>
<form action="<?= base_url('admin/insentif') ?>" method="GET" class="flex items-center gap-2">
    <input type="hidden" name="tab" value="<?= esc($tab) ?>">
    <input type="hidden" name="rate" value="<?= esc($rate) ?>">
    <select name="tahun" onchange="this.form.submit()" class="rounded-xl bg-slate-900 border border-slate-800 px-3 py-1.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-indigo-500/40 cursor-pointer">
        <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= $selectedYear === $y ? 'selected' : '' ?>>Tahun <?= $y ?></option>
        <?php endforeach; ?>
    </select>
</form>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Laporan Insentif</h1>
            <p class="text-xs text-slate-400">Estimasi pembagian insentif realisasi bagi Camat, Kepala Desa, dan Koordinator Lapangan</p>
        </div>
    </div>

    <!-- Parameter & Filter Panel -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg">
        <form action="<?= base_url('admin/insentif') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <input type="hidden" name="tab" value="<?= esc($tab) ?>">
            <input type="hidden" name="tahun" value="<?= esc($selectedYear) ?>">
            
            <div class="space-y-2">
                <label for="rate" class="text-xs font-semibold text-slate-300 flex justify-between">
                    <span>Persentase Insentif (%)</span>
                    <span class="text-indigo-400 font-bold" id="rate-display"><?= number_format($rate, 2) ?>%</span>
                </label>
                <div class="flex items-center gap-3">
                    <input type="range" id="rate" name="rate" min="0.1" max="25" step="0.1" value="<?= esc($rate) ?>" 
                           oninput="document.getElementById('rate-display').textContent = parseFloat(this.value).toFixed(2) + '%'; document.getElementById('rate_input').value = this.value;"
                           class="w-full h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                </div>
            </div>

            <div class="space-y-2">
                <label for="rate_input" class="text-xs font-semibold text-slate-300">Input Manual Persentase (%)</label>
                <div class="relative">
                    <input type="number" id="rate_input" step="0.01" min="0" max="100" value="<?= esc($rate) ?>"
                           oninput="document.getElementById('rate').value = this.value; document.getElementById('rate-display').textContent = parseFloat(this.value || 0).toFixed(2) + '%'"
                           class="w-full rounded-2xl bg-slate-950 border border-slate-800 px-4 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-indigo-500/40">
                    <span class="absolute right-4 top-2 text-xs text-slate-500">%</span>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-2xl transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20">
                    <i class="fa-solid fa-calculator"></i> Hitung Ulang
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Target Card -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-indigo-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Target</span>
                <div class="h-7 w-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-bullseye text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-base font-extrabold text-white">Rp <?= number_format($totalTarget, 0, ',', '.') ?></span>
                <span class="text-[10px] text-slate-500 block mt-0.5">Tahun <?= $selectedYear ?></span>
            </div>
        </div>

        <!-- Realisasi Card -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-emerald-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Realisasi</span>
                <div class="h-7 w-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-base font-extrabold text-emerald-400">Rp <?= number_format($totalRealisasi, 0, ',', '.') ?></span>
                <span class="text-[10px] text-slate-500 block mt-0.5">Realisasi Tahun <?= $selectedYear ?></span>
            </div>
        </div>

        <!-- Persentase Card -->
        <?php 
        $totalPersen = $totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0.0;
        ?>
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-sky-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Persentase Realisasi</span>
                <div class="h-7 w-7 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-base font-extrabold text-sky-400"><?= number_format($totalPersen, 2, ',', '.') ?>%</span>
                <div class="w-full bg-slate-800 rounded-full h-1 mt-1">
                    <div class="bg-sky-500 h-1 rounded-full" style="width: <?= min(100.0, $totalPersen) ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Insentif Card -->
        <?php 
        $totalInsentif = ($totalRealisasi * $rate) / 100;
        ?>
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden flex flex-col justify-between h-28">
            <div class="absolute -right-4 -top-4 w-12 h-12 bg-amber-500/10 rounded-full blur-xl"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Estimasi Insentif</span>
                <div class="h-7 w-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-gift text-xs"></i>
                </div>
            </div>
            <div>
                <span class="text-base font-extrabold text-amber-400">Rp <?= number_format($totalInsentif, 0, ',', '.') ?></span>
                <span class="text-[10px] text-slate-500 block mt-0.5">Berdasarkan rate <?= number_format($rate, 2) ?>%</span>
            </div>
        </div>
    </div>

    <!-- Tab Selector -->
    <div class="flex border-b border-slate-800">
        <a href="<?= base_url('admin/insentif?tab=camat&tahun=' . $selectedYear . '&rate=' . $rate) ?>" 
           class="px-6 py-3 text-sm font-bold border-b-2 transition-all duration-200 <?= $tab === 'camat' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-white' ?>">
            <i class="fa-solid fa-building mr-2"></i>Camat (Kecamatan)
        </a>
        <a href="<?= base_url('admin/insentif?tab=kades&tahun=' . $selectedYear . '&rate=' . $rate) ?>" 
           class="px-6 py-3 text-sm font-bold border-b-2 transition-all duration-200 <?= $tab === 'kades' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-white' ?>">
            <i class="fa-solid fa-tree mr-2"></i>Kepala Desa
        </a>
        <a href="<?= base_url('admin/insentif?tab=kolektor&tahun=' . $selectedYear . '&rate=' . $rate) ?>" 
           class="px-6 py-3 text-sm font-bold border-b-2 transition-all duration-200 <?= $tab === 'kolektor' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-white' ?>">
            <i class="fa-solid fa-users mr-2"></i>Kolektor
        </a>
    </div>

    <!-- Table Details -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-800/80 pb-4 gap-3">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid <?= $tab === 'camat' ? 'fa-building' : ($tab === 'kades' ? 'fa-tree' : 'fa-users') ?> text-indigo-400"></i>
                <span>Daftar Insentif <?= $tab === 'camat' ? 'Camat' : ($tab === 'kades' ? 'Kepala Desa' : 'Kolektor') ?></span>
            </h3>
            <a href="<?= base_url('admin/insentif/export?tab=' . $tab . '&tahun=' . $selectedYear . '&rate=' . $rate) ?>" 
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center gap-1.5 shadow-md shadow-emerald-500/10">
                <i class="fa-solid fa-file-excel"></i> Export Excel (.xls)
            </a>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3.5 px-4 w-16 text-center">No</th>
                        <?php if ($tab === 'camat'): ?>
                            <th class="py-3.5 px-4 w-28">Kode Kec.</th>
                            <th class="py-3.5 px-4">Nama Kecamatan</th>
                            <th class="py-3.5 px-4">Nama Camat</th>
                            <th class="py-3.5 px-4">No. Rekening</th>
                        <?php elseif ($tab === 'kades'): ?>
                            <th class="py-3.5 px-4 w-28">Kode Kec.</th>
                            <th class="py-3.5 px-4 w-28">Kode Desa</th>
                            <th class="py-3.5 px-4">Kecamatan</th>
                            <th class="py-3.5 px-4">Nama Desa</th>
                            <th class="py-3.5 px-4">Nama Kepala Desa</th>
                            <th class="py-3.5 px-4">No. Rekening Kades</th>
                            <th class="py-3.5 px-4">Nama Koordinator</th>
                            <th class="py-3.5 px-4">No. Rekening Koordinator</th>
                        <?php else: /* tab === 'kolektor' */ ?>
                            <th class="py-3.5 px-4 w-28">Kode Kec.</th>
                            <th class="py-3.5 px-4 w-28">Kode Desa</th>
                            <th class="py-3.5 px-4">Kecamatan</th>
                            <th class="py-3.5 px-4">Nama Desa</th>
                            <th class="py-3.5 px-4 w-28">Kode Kolektor</th>
                            <th class="py-3.5 px-4">Nama Kolektor</th>
                            <th class="py-3.5 px-4">No. Rekening Kolektor</th>
                        <?php endif; ?>
                        
                        <?php if ($tab === 'kades'): ?>
                            <th class="py-3.5 px-4 text-right">Target</th>
                            <th class="py-3.5 px-4 text-right">Realisasi</th>
                        <?php elseif ($tab === 'kolektor'): ?>
                            <th class="py-3.5 px-4 text-right">Realisasi</th>
                        <?php elseif ($tab !== 'camat'): ?>
                            <th class="py-3.5 px-4 text-right">Target</th>
                            <th class="py-3.5 px-4 text-right">Realisasi</th>
                            <th class="py-3.5 px-4 text-center w-28">% Capaian</th>
                            <th class="py-3.5 px-4 text-right text-indigo-400">Estimasi Insentif</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dataReport)): ?>
                        <tr>
                            <td colspan="<?= $tab === 'camat' ? 5 : ($tab === 'kades' ? 11 : 9) ?>" class="py-8 text-center text-slate-500 font-medium">
                                <i class="fa-solid fa-triangle-exclamation text-2xl block mb-2 text-slate-600"></i>
                                Tidak ada data untuk tahun terpilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($dataReport as $row): 
                            $target = (float)$row['target'];
                            $realisasi = (float)$row['realisasi'];
                            $persen = $target > 0 ? ($realisasi / $target) * 100 : 0.0;
                            $insentifRow = ($realisasi * $rate) / 100;
                        ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/20 transition-colors duration-200">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                
                                <?php if ($tab === 'camat'): ?>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($row['kd_kecamatan']) ?></td>
                                    <td class="py-3.5 px-4 text-white font-bold"><?= esc($row['nm_kecamatan']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-semibold">
                                        <?= $row['nm_camat'] ? esc($row['nm_camat']) : '<span class="text-slate-600 font-normal italic">Belum diatur</span>' ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">
                                        <?= $row['norek_camat'] ? esc($row['norek_camat']) : '<span class="text-slate-600">-</span>' ?>
                                    </td>
                                <?php elseif ($tab === 'kades'): ?>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($row['kd_kecamatan']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($row['kd_desa']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-400 font-semibold text-xs"><?= esc($row['nm_kecamatan']) ?></td>
                                    <td class="py-3.5 px-4 text-white font-bold"><?= esc($row['nm_desa']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-semibold">
                                        <?= $row['nm_kepala_desa'] ? esc($row['nm_kepala_desa']) : '<span class="text-slate-600 font-normal italic">Belum diatur</span>' ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">
                                        <?= $row['norek_kepala_desa'] ? esc($row['norek_kepala_desa']) : '<span class="text-slate-600">-</span>' ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-300 font-semibold">
                                        <?= $row['nm_koordinator'] ? esc($row['nm_koordinator']) : '<span class="text-slate-600 font-normal italic">Belum diatur</span>' ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">
                                        <?= $row['norek_koordinator'] ? esc($row['norek_koordinator']) : '<span class="text-slate-600">-</span>' ?>
                                    </td>
                                <?php else: /* tab === 'kolektor' */ ?>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($row['kd_kecamatan']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($row['kd_desa']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-400 font-semibold text-xs"><?= esc($row['nm_kecamatan']) ?></td>
                                    <td class="py-3.5 px-4 text-white font-bold"><?= esc($row['nm_desa']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($row['kd_kolektor']) ?></td>
                                    <td class="py-3.5 px-4 text-white font-bold"><?= esc($row['nm_kolektor']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">
                                        <?= $row['norek_kolektor'] ? esc($row['norek_kolektor']) : '<span class="text-slate-600">-</span>' ?>
                                    </td>
                                <?php endif; ?>

                                <?php if ($tab === 'kades'): ?>
                                    <td class="py-3.5 px-4 text-right text-slate-300 font-medium font-mono text-xs">
                                        Rp <?= number_format($target, 0, ',', '.') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-emerald-450 font-semibold font-mono text-xs">
                                        Rp <?= number_format($realisasi, 0, ',', '.') ?>
                                    </td>
                                <?php elseif ($tab === 'kolektor'): ?>
                                    <td class="py-3.5 px-4 text-right text-emerald-450 font-semibold font-mono text-xs">
                                        Rp <?= number_format($realisasi, 0, ',', '.') ?>
                                    </td>
                                <?php elseif ($tab !== 'camat'): ?>
                                    <td class="py-3.5 px-4 text-right text-slate-300 font-medium font-mono text-xs">
                                        Rp <?= number_format($target, 0, ',', '.') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-emerald-450 font-semibold font-mono text-xs">
                                        Rp <?= number_format($realisasi, 0, ',', '.') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $persen >= 100 ? 'bg-emerald-500/10 text-emerald-400' : ($persen >= 80 ? 'bg-indigo-500/10 text-indigo-400' : ($persen > 0 ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-800 text-slate-500')) ?>">
                                            <?= number_format($persen, 2, ',', '.') ?>%
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-indigo-350 font-bold font-mono text-xs">
                                        Rp <?= number_format($insentifRow, 0, ',', '.') ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
