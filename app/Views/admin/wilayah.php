<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Master Data Wilayah</h1>
            <p class="text-xs text-slate-400">Kelola informasi Camat (Kecamatan) dan Kepala Desa (Desa)</p>
        </div>
    </div>

    <!-- Tab Selector -->
    <div class="flex border-b border-slate-800">
        <a href="<?= base_url('admin/wilayah?tab=kecamatan') ?>" 
           class="px-6 py-3 text-sm font-bold border-b-2 transition-all duration-200 <?= $tab === 'kecamatan' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-white' ?>">
            <i class="fa-solid fa-building mr-2"></i>Kecamatan
        </a>
        <a href="<?= base_url('admin/wilayah?tab=desa') ?>" 
           class="px-6 py-3 text-sm font-bold border-b-2 transition-all duration-200 <?= $tab === 'desa' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-white' ?>">
            <i class="fa-solid fa-tree mr-2"></i>Desa
        </a>
    </div>

    <?php if ($tab === 'kecamatan'): ?>
        <!-- KECAMATAN TAB -->
        <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm border-collapse min-w-[700px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                            <th class="py-3.5 px-4 w-16 text-center">No</th>
                            <th class="py-3.5 px-4 w-28">Kode Kec.</th>
                            <th class="py-3.5 px-4">Nama Kecamatan</th>
                            <th class="py-3.5 px-4">Nama Camat</th>
                            <th class="py-3.5 px-4">No. Rekening Camat</th>
                            <th class="py-3.5 px-4 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($kecamatans as $kec): 
                        ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/20 transition-colors duration-200">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($kec['kd_kecamatan']) ?></td>
                                <td class="py-3.5 px-4 text-white font-bold"><?= esc($kec['nm_kecamatan']) ?></td>
                                <td class="py-3.5 px-4 text-slate-300 font-semibold"><?= $kec['nm_camat'] ? esc($kec['nm_camat']) : '<span class="text-slate-600 font-normal">-</span>' ?></td>
                                <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= $kec['norek_camat'] ? esc($kec['norek_camat']) : '<span class="text-slate-600 font-normal">-</span>' ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-center">
                                        <button onclick='openKecModal(<?= json_encode($kec) ?>)' 
                                                class="h-8 w-8 rounded-lg bg-indigo-500/10 hover:bg-indigo-500 text-indigo-400 hover:text-white flex items-center justify-center border border-indigo-500/20 transition-all cursor-pointer"
                                                title="Edit Camat">
                                            <i class="fa-solid fa-pencil text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <!-- DESA TAB -->
        <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
            
            <!-- Filters -->
            <form action="<?= base_url('admin/wilayah') ?>" method="GET" class="flex flex-col sm:flex-row items-center gap-3 bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60">
                <input type="hidden" name="tab" value="desa">
                
                <!-- Filter Kecamatan -->
                <div class="w-full sm:w-64">
                    <select name="kecamatan_id" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php foreach ($kecamatans as $kec): ?>
                            <option value="<?= $kec['kecamatan_id'] ?>" <?= $selectedKec == $kec['kecamatan_id'] ? 'selected' : '' ?>>
                                <?= esc($kec['nm_kecamatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search_desa" value="<?= esc($searchDesa) ?>" placeholder="Cari nama desa..."
                           class="w-full rounded-xl bg-slate-950 border border-slate-800 pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/40">
                </div>

                <div class="flex w-full sm:w-auto gap-2">
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-md shadow-indigo-500/10">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    
                    <?php if (!empty($selectedKec) || !empty($searchDesa)): ?>
                        <a href="<?= base_url('admin/wilayah?tab=desa') ?>" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm border-collapse min-w-[700px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                            <th class="py-3.5 px-4 w-16 text-center">No</th>
                            <th class="py-3.5 px-4 w-28">Kode Desa</th>
                            <th class="py-3.5 px-4">Nama Desa</th>
                            <th class="py-3.5 px-4">Kecamatan</th>
                            <th class="py-3.5 px-4">Nama Kepala Desa</th>
                            <th class="py-3.5 px-4">No. Rekening Kades</th>
                            <th class="py-3.5 px-4">Nama Koordinator</th>
                            <th class="py-3.5 px-4">No. Rekening Koordinator</th>
                            <th class="py-3.5 px-4 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($desas)): ?>
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-500 font-medium">
                                    <i class="fa-solid fa-tree-slash text-2xl block mb-2 text-slate-600"></i>
                                    Tidak ada data desa terdaftar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $no = ($currentPage - 1) * 10 + 1;
                            foreach ($desas as $d): 
                            ?>
                                <tr class="border-b border-slate-800/60 hover:bg-slate-800/20 transition-colors duration-200">
                                    <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= esc($d['kd_desa']) ?></td>
                                    <td class="py-3.5 px-4 text-white font-bold"><?= esc($d['nm_desa']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-400 font-semibold text-xs"><?= esc($d['nm_kecamatan']) ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-semibold"><?= $d['nm_kepala_desa'] ? esc($d['nm_kepala_desa']) : '<span class="text-slate-600 font-normal">-</span>' ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= $d['norek_kepala_desa'] ? esc($d['norek_kepala_desa']) : '<span class="text-slate-600 font-normal">-</span>' ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-semibold"><?= $d['nm_koordinator'] ? esc($d['nm_koordinator']) : '<span class="text-slate-600 font-normal">-</span>' ?></td>
                                    <td class="py-3.5 px-4 text-slate-300 font-mono text-xs"><?= $d['norek_koordinator'] ? esc($d['norek_koordinator']) : '<span class="text-slate-600 font-normal">-</span>' ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center justify-center">
                                            <button onclick='openDesaModal(<?= json_encode($d) ?>)' 
                                                    class="h-8 w-8 rounded-lg bg-indigo-500/10 hover:bg-indigo-500 text-indigo-400 hover:text-white flex items-center justify-center border border-indigo-500/20 transition-all cursor-pointer"
                                                    title="Edit Desa">
                                                <i class="fa-solid fa-pencil text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-800/80 pt-4">
                    <span class="text-xs text-slate-400 font-medium">
                        Menampilkan halaman <span class="font-bold text-slate-200"><?= $currentPage ?></span> dari <span class="font-bold text-slate-200"><?= $totalPages ?></span> (<span class="font-semibold"><?= $totalItems ?></span> desa)
                    </span>
                    <div class="flex items-center gap-1">
                        <!-- Prev Button -->
                        <a href="<?= base_url('admin/wilayah?tab=desa&page=' . ($currentPage - 1) . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($searchDesa ? '&search_desa=' . urlencode($searchDesa) : '')) ?>" 
                           class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                            <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                        </a>
                        
                        <!-- Page Numbers -->
                        <?php for($i=1; $i<=$totalPages; $i++): ?>
                            <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)): ?>
                                <a href="<?= base_url('admin/wilayah?tab=desa&page=' . $i . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($searchDesa ? '&search_desa=' . urlencode($searchDesa) : '')) ?>" 
                                   class="h-8 w-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all border <?= $i === $currentPage ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-white' ?>">
                                    <?= $i ?>
                                </a>
                            <?php elseif ($i == 2 || $i == $totalPages - 1): ?>
                                <span class="text-slate-600 px-1 text-xs">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <a href="<?= base_url('admin/wilayah?tab=desa&page=' . ($currentPage + 1) . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($searchDesa ? '&search_desa=' . urlencode($searchDesa) : '')) ?>" 
                           class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>">
                            Next <i class="fa-solid fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

</div>

<!-- MODAL KECAMATAN (EDIT CAMAT) -->
<div id="kec-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md glass-panel rounded-3xl shadow-2xl overflow-hidden relative border border-slate-800">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-building text-indigo-400"></i>
                <span>Edit Informasi Camat</span>
            </h3>
            <button type="button" onclick="closeKecModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <form action="<?= base_url('admin/wilayah/kecamatan/save') ?>" method="POST" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" id="form-kec-id" name="kecamatan_id">

            <!-- Read-only info -->
            <div class="rounded-2xl bg-slate-900/50 border border-slate-800 p-4 text-xs space-y-1 text-slate-400">
                <p>Kecamatan: <span id="info-kec-nama" class="font-bold text-white"></span></p>
                <p>Kode Kecamatan: <span id="info-kec-kode" class="font-bold text-white font-mono"></span></p>
            </div>

            <!-- Nama Camat -->
            <div class="space-y-1.5">
                <label for="form-camat-nama" class="text-xs font-semibold text-slate-300">Nama Camat</label>
                <input type="text" id="form-camat-nama" name="nm_camat" placeholder="Masukkan nama camat"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Norek Camat -->
            <div class="space-y-1.5">
                <label for="form-camat-norek" class="text-xs font-semibold text-slate-300">No. Rekening Camat</label>
                <input type="text" id="form-camat-norek" name="norek_camat" placeholder="Masukkan nomor rekening camat"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-mono">
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2.5 border-t border-slate-800/80 pt-4 mt-6">
                <button type="button" onclick="closeKecModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DESA (EDIT KEPALA DESA) -->
<div id="desa-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md glass-panel rounded-3xl shadow-2xl overflow-hidden relative border border-slate-800">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-tree text-indigo-400"></i>
                <span>Edit Informasi Kepala Desa</span>
            </h3>
            <button type="button" onclick="closeDesaModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <form action="<?= base_url('admin/wilayah/desa/save') ?>" method="POST" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" id="form-desa-id" name="desa_id">
            
            <!-- Pass active filters so redirect maintains state -->
            <input type="hidden" name="redirect_kecamatan_id" value="<?= esc($selectedKec) ?>">
            <input type="hidden" name="redirect_search_desa" value="<?= esc($searchDesa) ?>">
            <input type="hidden" name="redirect_page" value="<?= esc($currentPage) ?>">

            <!-- Read-only info -->
            <div class="rounded-2xl bg-slate-900/50 border border-slate-800 p-4 text-xs space-y-1 text-slate-400">
                <p>Desa: <span id="info-desa-nama" class="font-bold text-white"></span></p>
                <p>Kecamatan: <span id="info-desa-kec" class="font-bold text-white"></span></p>
                <p>Kode Desa: <span id="info-desa-kode" class="font-bold text-white font-mono"></span></p>
            </div>

            <!-- Nama Kepala Desa -->
            <div class="space-y-1.5">
                <label for="form-kades-nama" class="text-xs font-semibold text-slate-300">Nama Kepala Desa</label>
                <input type="text" id="form-kades-nama" name="nm_kepala_desa" placeholder="Masukkan nama kepala desa"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Norek Kepala Desa -->
            <div class="space-y-1.5">
                <label for="form-kades-norek" class="text-xs font-semibold text-slate-300">No. Rekening Kepala Desa</label>
                <input type="text" id="form-kades-norek" name="norek_kepala_desa" placeholder="Masukkan nomor rekening kepala desa"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-mono">
            </div>

            <!-- Nama Koordinator -->
            <div class="space-y-1.5">
                <label for="form-koordinator-nama" class="text-xs font-semibold text-slate-300">Nama Koordinator</label>
                <input type="text" id="form-koordinator-nama" name="nm_koordinator" placeholder="Masukkan nama koordinator"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Norek Koordinator -->
            <div class="space-y-1.5">
                <label for="form-koordinator-norek" class="text-xs font-semibold text-slate-300">No. Rekening Koordinator</label>
                <input type="text" id="form-koordinator-norek" name="norek_koordinator" placeholder="Masukkan nomor rekening koordinator"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-mono">
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2.5 border-t border-slate-800/80 pt-4 mt-6">
                <button type="button" onclick="closeDesaModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Kecamatan Modal Elements
    const kecModal = document.getElementById('kec-modal');
    const formKecId = document.getElementById('form-kec-id');
    const infoKecNama = document.getElementById('info-kec-nama');
    const infoKecKode = document.getElementById('info-kec-kode');
    const formCamatNama = document.getElementById('form-camat-nama');
    const formCamatNorek = document.getElementById('form-camat-norek');

    // Desa Modal Elements
    const desaModal = document.getElementById('desa-modal');
    const formDesaId = document.getElementById('form-desa-id');
    const infoDesaNama = document.getElementById('info-desa-nama');
    const infoDesaKec = document.getElementById('info-desa-kec');
    const infoDesaKode = document.getElementById('info-desa-kode');
    const formKadesNama = document.getElementById('form-kades-nama');
    const formKadesNorek = document.getElementById('form-kades-norek');
    const formKoordinatorNama = document.getElementById('form-koordinator-nama');
    const formKoordinatorNorek = document.getElementById('form-koordinator-norek');

    // Kecamatan actions
    function openKecModal(kec) {
        formKecId.value = kec.kecamatan_id;
        infoKecNama.textContent = kec.nm_kecamatan;
        infoKecKode.textContent = kec.kd_kecamatan;
        formCamatNama.value = kec.nm_camat || '';
        formCamatNorek.value = kec.norek_camat || '';
        kecModal.classList.remove('hidden');
    }

    function closeKecModal() {
        kecModal.classList.add('hidden');
    }

    // Desa actions
    function openDesaModal(desa) {
        formDesaId.value = desa.desa_id;
        infoDesaNama.textContent = desa.nm_desa;
        infoDesaKec.textContent = desa.nm_kecamatan;
        infoDesaKode.textContent = desa.kd_desa;
        formKadesNama.value = desa.nm_kepala_desa || '';
        formKadesNorek.value = desa.norek_kepala_desa || '';
        formKoordinatorNama.value = desa.nm_koordinator || '';
        formKoordinatorNorek.value = desa.norek_koordinator || '';
        desaModal.classList.remove('hidden');
    }

    function closeDesaModal() {
        desaModal.classList.add('hidden');
    }
</script>
<?= $this->endSection() ?>
