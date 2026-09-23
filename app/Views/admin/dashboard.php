<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dashboard Capaian & Realisasi PBB<?= $this->endSection() ?>

<?= $this->section('page_header') ?>Dashboard Capaian & Realisasi PBB<?= $this->endSection() ?>

<?= $this->section('header_actions') ?>
<form method="GET" action="<?= base_url('admin/dashboard') ?>" class="flex items-center gap-2">
    <?php if ($kecId): ?><input type="hidden" name="kecamatan_id" value="<?= esc($kecId) ?>"><?php endif; ?>
    <?php if ($desaId): ?><input type="hidden" name="desa_id" value="<?= esc($desaId) ?>"><?php endif; ?>
    <label for="header-year" class="text-xs font-semibold text-slate-400 flex items-center gap-1">
        <i class="fa-regular fa-calendar-days text-slate-500"></i> Tahun:
    </label>
    <select id="header-year" name="tahun" onchange="this.form.submit()" class="rounded-xl bg-slate-800 border border-slate-700 px-3 py-1.5 text-xs font-bold text-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
        <?php foreach ($years as $yr): ?>
            <option value="<?= $yr ?>" <?= $yr === $selectedYear ? 'selected' : '' ?>><?= $yr ?></option>
        <?php endforeach; ?>
    </select>
</form>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Top Filter Panel -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg">
        <form method="GET" action="<?= base_url('admin/dashboard') ?>" class="grid grid-cols-1 md:grid-cols-3 items-end gap-4">
            <input type="hidden" name="tahun" value="<?= esc($selectedYear) ?>">
            
            <!-- Filter Kecamatan -->
            <div class="space-y-1.5">
                <label for="kec-filter" class="text-xs font-semibold text-slate-300">Filter Kecamatan</label>
                <select id="kec-filter" name="kecamatan_id" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    <option value="">-- Semua Kecamatan --</option>
                    <?php foreach ($kecamatans as $kec): ?>
                        <option value="<?= $kec['kecamatan_id'] ?>" <?= $kec['kecamatan_id'] == $kecId ? 'selected' : '' ?>>
                            <?= esc($kec['nm_kecamatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Desa -->
            <div class="space-y-1.5">
                <label for="desa-filter" class="text-xs font-semibold text-slate-300">Filter Desa</label>
                <select id="desa-filter" name="desa_id" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40" <?= empty($desasFiltered) && !$kecId ? 'disabled' : '' ?>>
                    <option value="">-- Semua Desa --</option>
                    <?php foreach ($desasFiltered as $d): ?>
                        <option value="<?= $d['desa_id'] ?>" <?= $d['desa_id'] == $desaId ? 'selected' : '' ?>>
                            <?= esc($d['nm_desa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2.5">
                <button type="submit" class="flex-grow py-2.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 active:scale-[0.98] transition-all cursor-pointer">
                    <i class="fa-solid fa-filter mr-1.5 text-xs"></i> Terapkan Filter
                </button>
                <?php if ($kecId || $desaId): ?>
                    <a href="<?= base_url('admin/dashboard?tahun=' . $selectedYear) ?>" class="py-2.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Ringkasan Area Terpilih Cards -->
    <div class="space-y-2">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Ringkasan Area Terpilih (Tahun <?= esc($selectedYear) ?>)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Target Card -->
            <div class="glass-panel rounded-2xl p-5 shadow flex items-center gap-4 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-blue-500/5 rounded-full"></div>
                <div class="h-10 w-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <div class="leading-tight">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Target PBB</span>
                    <span class="text-base font-extrabold text-white">Rp <?= number_format($totalTarget, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Realisasi Card -->
            <div class="glass-panel rounded-2xl p-5 shadow flex items-center gap-4 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-emerald-500/5 rounded-full"></div>
                <div class="h-10 w-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="leading-tight">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Realisasi</span>
                    <span class="text-base font-extrabold text-white">Rp <?= number_format($totalRealisasi, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Sisa Target Card -->
            <div class="glass-panel rounded-2xl p-5 shadow flex items-center gap-4 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-rose-500/5 rounded-full"></div>
                <div class="h-10 w-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="leading-tight">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Sisa Target</span>
                    <span class="text-base font-extrabold text-white">Rp <?= number_format($totalSisa, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Persentase Card -->
            <div class="glass-panel rounded-2xl p-5 shadow flex items-center gap-4 relative overflow-hidden">
                <?php 
                    $cardColor = 'text-rose-400 bg-rose-500/10 border-rose-500/20';
                    $progressColor = 'bg-rose-500';
                    if ($totalPersen >= 80) {
                        $cardColor = 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
                        $progressColor = 'bg-emerald-500';
                    } elseif ($totalPersen >= 50) {
                        $cardColor = 'text-amber-400 bg-amber-500/10 border-amber-500/20';
                        $progressColor = 'bg-amber-500';
                    }
                ?>
                <div class="absolute -right-4 -bottom-4 w-16 h-16 <?= $progressColor ?>/5 rounded-full"></div>
                <div class="h-10 w-10 rounded-xl <?= $cardColor ?> border flex items-center justify-center text-lg font-bold flex-shrink-0">
                    <?= number_format($totalPersen, 0) ?>%
                </div>
                <div class="leading-tight flex-grow">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Rasio Capaian</span>
                    <span class="text-base font-extrabold text-white block mb-1"><?= number_format($totalPersen, 2, ',', '.') ?>%</span>
                    <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                        <div class="h-full <?= $progressColor ?> rounded-full" style="width: <?= min(100.0, $totalPersen) ?>%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Drill-down Table -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
        <div class="border-b border-slate-800 pb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-400"></i>
                Tabel Rincian Capaian Wilayah / Kolektor
            </h3>
            <p class="text-xs text-slate-400">Klik baris Kecamatan atau Desa untuk memperluas (drill-down) data.</p>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3.5 px-4">Wilayah / Kolektor</th>
                        <th class="py-3.5 px-4 text-right">Target</th>
                        <th class="py-3.5 px-4 text-right">Realisasi</th>
                        <th class="py-3.5 px-4 text-right">Sisa</th>
                        <th class="py-3.5 px-4 text-center w-24">Persen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($drilldown)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 font-medium">
                                <i class="fa-solid fa-folder-open text-2xl block mb-2"></i>
                                Tidak ada data target untuk tahun <?= esc($selectedYear) ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($drilldown as $kecId => $kec): ?>
                            
                            <!-- Level 1: Kecamatan Row -->
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/30 transition-colors duration-200 font-semibold cursor-pointer group"
                                onclick="toggleKec(<?= $kecId ?>)">
                                <td class="py-3.5 px-4 flex items-center text-slate-200 group-hover:text-white transition-colors">
                                    <span class="mr-2.5 h-6 w-6 rounded-lg bg-slate-800 group-hover:bg-slate-700 flex items-center justify-center text-[10px] text-slate-400 group-hover:text-white transition-all transform duration-200" id="icon-container-kec-<?= $kecId ?>">
                                        <i class="fa-solid fa-chevron-right" id="icon-kec-<?= $kecId ?>"></i>
                                    </span>
                                    <span><?= esc($kec['name']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-right text-slate-200 font-bold">Rp <?= number_format($kec['target'], 0, ',', '.') ?></td>
                                <td class="py-3.5 px-4 text-right text-emerald-400 font-bold">Rp <?= number_format($kec['realisasi'], 0, ',', '.') ?></td>
                                <td class="py-3.5 px-4 text-right text-slate-400 font-medium">Rp <?= number_format($kec['sisa'], 0, ',', '.') ?></td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php
                                        $kecColor = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                                        if ($kec['persen'] >= 80) $kecColor = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                                        elseif ($kec['persen'] >= 50) $kecColor = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                                    ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold border <?= $kecColor ?>">
                                        <?= number_format($kec['persen'], 2) ?>%
                                    </span>
                                </td>
                            </tr>

                            <?php foreach ($kec['desas'] as $dId => $desa): ?>
                                
                                <!-- Level 2: Desa Row -->
                                <tr class="kec-child-<?= $kecId ?> hidden border-b border-slate-800/40 hover:bg-slate-800/20 transition-colors duration-200 cursor-pointer group/desa pl-6 bg-slate-900/20"
                                    data-kec-parent="<?= $kecId ?>"
                                    onclick="toggleDesa(<?= $dId ?>, event)">
                                    <td class="py-3 px-4 pl-12 flex items-center text-slate-300 group-hover/desa:text-white transition-colors">
                                        <span class="mr-2 h-5 w-5 rounded bg-slate-900 group-hover/desa:bg-slate-800 flex items-center justify-center text-[9px] text-slate-500 group-hover/desa:text-white transition-all transform duration-200" id="icon-container-desa-<?= $dId ?>">
                                            <i class="fa-solid fa-chevron-right" id="icon-desa-<?= $dId ?>"></i>
                                        </span>
                                        <span class="text-sm font-semibold"><?= esc($desa['name']) ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-slate-300 text-sm">Rp <?= number_format($desa['target'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-4 text-right text-emerald-400/90 text-sm">Rp <?= number_format($desa['realisasi'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-4 text-right text-slate-400/80 text-sm">Rp <?= number_format($desa['sisa'], 0, ',', '.') ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php
                                            $desaColor = 'bg-rose-500/5 text-rose-400 border-rose-500/10';
                                            if ($desa['persen'] >= 80) $desaColor = 'bg-emerald-500/5 text-emerald-400 border-emerald-500/10';
                                            elseif ($desa['persen'] >= 50) $desaColor = 'bg-amber-500/5 text-amber-400 border-amber-500/10';
                                        ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold border <?= $desaColor ?>">
                                            <?= number_format($desa['persen'], 2) ?>%
                                        </span>
                                    </td>
                                </tr>

                                <?php if (empty($desa['kolektors'])): ?>
                                    <!-- No Collectors Row -->
                                    <tr class="kec-child-<?= $kecId ?> desa-child-<?= $dId ?> hidden border-b border-slate-800/20 bg-slate-950/20"
                                        data-kec-parent="<?= $kecId ?>"
                                        data-desa-parent="<?= $dId ?>">
                                        <td colspan="5" class="py-2.5 px-4 pl-20 text-xs text-slate-500 italic">
                                            <i class="fa-solid fa-circle-info mr-1.5 text-[10px]"></i> Belum ada kolektor terdaftar di desa ini pada tahun <?= esc($selectedYear) ?>.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($desa['kolektors'] as $kolektor): ?>
                                        
                                        <!-- Level 3: Kolektor Row (Target hanya sampai desa/kelurahan, kolektor hanya memiliki realisasi) -->
                                        <tr class="kec-child-<?= $kecId ?> desa-child-<?= $dId ?> hidden border-b border-slate-800/20 hover:bg-slate-800/10 transition-colors duration-200 bg-slate-950/20"
                                            data-kec-parent="<?= $kecId ?>"
                                            data-desa-parent="<?= $dId ?>">
                                            <td class="py-2.5 px-4 pl-20 flex items-center text-slate-400">
                                                <?php if (strpos($kolektor['name'], 'Non-Kolektor') !== false || strpos($kolektor['name'], 'Langsung') !== false): ?>
                                                    <i class="fa-solid fa-building-columns text-amber-400/80 mr-2 text-xs"></i>
                                                    <span class="text-xs font-semibold text-amber-300"><?= esc($kolektor['name']) ?></span>
                                                <?php else: ?>
                                                    <i class="fa-solid fa-circle-user text-indigo-400/60 mr-2 text-xs"></i>
                                                    <span class="text-xs font-medium text-slate-300"><?= esc($kolektor['name']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-2.5 px-4 text-center text-slate-600 text-xs font-mono">-</td>
                                            <td class="py-2.5 px-4 text-right text-emerald-400 font-semibold text-xs font-mono">Rp <?= number_format($kolektor['realisasi'], 0, ',', '.') ?></td>
                                            <td class="py-2.5 px-4 text-center text-slate-600 text-xs font-mono">-</td>
                                            <td class="py-2.5 px-4 text-center text-slate-600 text-xs font-mono">-</td>
                                        </tr>

                                    <?php endforeach; ?>
                                <?php endif; ?>

                            <?php endforeach; ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Handles toggling Kecamatan child rows
    function toggleKec(kecId) {
        const childRows = document.querySelectorAll(`.kec-child-${kecId}`);
        const icon = document.getElementById(`icon-kec-${kecId}`);
        const iconContainer = document.getElementById(`icon-container-kec-${kecId}`);
        
        const isCollapsed = icon.classList.contains('fa-chevron-right');

        if (isCollapsed) {
            // Expand
            childRows.forEach(row => {
                // Show desas first, keep collectors hidden
                if (row.hasAttribute('data-desa-parent')) {
                    row.classList.add('hidden');
                } else {
                    row.classList.remove('hidden');
                }
            });
            icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
            iconContainer.classList.replace('bg-slate-800', 'bg-indigo-600');
            iconContainer.classList.replace('text-slate-400', 'text-white');
        } else {
            // Collapse all descendants
            childRows.forEach(row => {
                row.classList.add('hidden');
                if (row.hasAttribute('data-desa-id')) {
                    // reset desa chevrons too
                    const dId = row.getAttribute('onclick').match(/\d+/)[0];
                    const dIcon = document.getElementById(`icon-desa-${dId}`);
                    const dIconContainer = document.getElementById(`icon-container-desa-${dId}`);
                    if (dIcon) dIcon.classList.replace('fa-chevron-down', 'fa-chevron-right');
                    if (dIconContainer) {
                        dIconContainer.classList.replace('bg-sky-600', 'bg-slate-900');
                        dIconContainer.classList.replace('text-white', 'text-slate-500');
                    }
                }
            });
            icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
            iconContainer.classList.replace('bg-indigo-600', 'bg-slate-800');
            iconContainer.classList.replace('text-white', 'text-slate-400');
        }
    }

    // Handles toggling Desa child rows (Kolektors)
    function toggleDesa(desaId, event) {
        // Prevent click event bubbling up (though they are not nested in HTML table DOM elements)
        event.stopPropagation();
        
        const childRows = document.querySelectorAll(`.desa-child-${desaId}`);
        const icon = document.getElementById(`icon-desa-${desaId}`);
        const iconContainer = document.getElementById(`icon-container-desa-${desaId}`);
        
        const isCollapsed = icon.classList.contains('fa-chevron-right');

        if (isCollapsed) {
            // Expand
            childRows.forEach(row => {
                row.classList.remove('hidden');
            });
            icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
            iconContainer.classList.replace('bg-slate-900', 'bg-sky-600');
            iconContainer.classList.replace('text-slate-500', 'text-white');
        } else {
            // Collapse
            childRows.forEach(row => {
                row.classList.add('hidden');
            });
            icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
            iconContainer.classList.replace('bg-sky-600', 'bg-slate-900');
            iconContainer.classList.replace('text-white', 'text-slate-500');
        }
    }

    // Dynamic Dependent Dropdown for Filters
    document.addEventListener('DOMContentLoaded', () => {
        const kecSelect = document.getElementById('kec-filter');
        const desaSelect = document.getElementById('desa-filter');

        kecSelect.addEventListener('change', async (e) => {
            const kecId = e.target.value;
            
            desaSelect.innerHTML = '<option value="">-- Semua Desa --</option>';
            
            if (!kecId) {
                desaSelect.disabled = true;
                return;
            }

            desaSelect.disabled = false;
            try {
                const response = await fetch(`<?= base_url('admin/kolektor/get-desas') ?>?kecamatan_id=${kecId}`);
                if (!response.ok) throw new Error('Gagal mengambil data desa.');
                
                const desas = await response.json();
                desas.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.desa_id;
                    option.textContent = d.nm_desa;
                    desaSelect.appendChild(option);
                });
            } catch (err) {
                console.error(err);
            }
        });
    });
</script>
<?= $this->endSection() ?>
