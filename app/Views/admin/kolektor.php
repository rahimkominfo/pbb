<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Manajemen Data Kolektor<?= $this->endSection() ?>

<?= $this->section('page_header') ?>Manajemen Data Kolektor<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Include Select2 CSS via CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        background-color: #0f172a !important; /* Slate 900 */
        border-color: #334155 !important; /* Slate 700 */
        border-radius: 1rem !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f1f5f9 !important; /* Slate 100 */
        font-size: 0.875rem !important;
        padding-left: 1rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 10px !important;
    }
    .select2-dropdown {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        border-radius: 1rem !important;
        overflow: hidden;
        z-index: 9999 !important;
    }
    .select2-search__field {
        background-color: #1e293b !important;
        border-color: #475569 !important;
        color: #fff !important;
        border-radius: 0.5rem !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #312e81 !important;
        color: #fff !important;
    }
    .select2-results__option {
        color: #cbd5e1 !important;
        font-size: 0.875rem !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Control Panel: Search & Filter & Action Buttons -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            
            <!-- Search & Filter Form -->
            <form method="GET" action="<?= base_url('admin/kolektor') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 flex-grow max-w-6xl">
                <!-- Filter Tahun -->
                <div class="space-y-1.5">
                    <label for="tahun-select" class="text-xs font-semibold text-slate-300">Filter Tahun</label>
                    <select id="tahun-select" name="tahun" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer font-bold">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>>Tahun <?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="space-y-1.5">
                    <label for="search-input" class="text-xs font-semibold text-slate-300">Cari Kolektor</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" id="search-input" name="search" value="<?= esc($search) ?>" placeholder="Nama kolektor..." 
                               class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-9 pr-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    </div>
                </div>

                <!-- Filter Kecamatan -->
                <div class="space-y-1.5">
                    <label for="kec-select" class="text-xs font-semibold text-slate-300">Filter Kecamatan</label>
                    <select id="kec-select" name="kecamatan_id" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php foreach ($kecamatans as $kec): ?>
                            <option value="<?= $kec['kecamatan_id'] ?>" <?= $kec['kecamatan_id'] == $selectedKec ? 'selected' : '' ?>>
                                <?= esc($kec['nm_kecamatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Desa -->
                <div class="space-y-1.5">
                    <label for="desa-select" class="text-xs font-semibold text-slate-300">Filter Desa</label>
                    <select id="desa-select" name="desa_id" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer" <?= empty($selectedKec) ? 'disabled' : '' ?>>
                        <option value="">-- Semua Desa --</option>
                        <?php if (!empty($selectedKec)): ?>
                            <?php foreach ($desas as $d): ?>
                                <option value="<?= $d['desa_id'] ?>" <?= $d['desa_id'] == $selectedDesa ? 'selected' : '' ?>>
                                    <?= esc($d['nm_desa']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Action Button inside form -->
                <div class="flex gap-2 items-end">
                    <button type="submit" class="flex-grow py-2.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 active:scale-[0.98] transition-all cursor-pointer">
                        Cari
                    </button>
                    <?php if ($search || $selectedKec || $selectedDesa): ?>
                        <a href="<?= base_url('admin/kolektor?tahun=' . $selectedYear) ?>" class="py-2.5 px-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all" title="Reset filter pencarian">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>

            </form>

            <!-- Action Buttons: Salin & Tambah Kolektor -->
            <div class="flex items-center gap-2">
                <!-- Copy Collectors from previous year button -->
                <button type="button" onclick="openCopyModal()" 
                        class="py-2.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 hover:border-slate-600 font-bold text-sm tracking-wide shadow-md transition-all cursor-pointer flex items-center justify-center gap-2"
                        title="Salin kolektor dari tahun lain">
                    <i class="fa-solid fa-copy text-indigo-400 text-xs"></i>
                    <span>Salin Kolektor</span>
                </button>

                <!-- Add Kolektor Button -->
                <button type="button" onclick="openAddModal()" 
                        class="py-2.5 px-5 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/35 transition-all cursor-pointer flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah Kolektor</span>
                </button>
            </div>

        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm border-collapse min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3.5 px-4 w-16 text-center">No</th>
                        <th class="py-3.5 px-4 w-24 text-center">Tahun</th>
                        <th class="py-3.5 px-4 w-20">Kode</th>
                        <th class="py-3.5 px-4">Nama Kolektor</th>
                        <th class="py-3.5 px-4">Desa (Kecamatan)</th>
                        <th class="py-3.5 px-4">Dusun</th>
                        <th class="py-3.5 px-4 w-40">No. Rekening</th>
                        <th class="py-3.5 px-4 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kolektors)): ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 font-medium">
                                <i class="fa-solid fa-users-slash text-2xl block mb-2"></i>
                                Tidak ada data kolektor ditemukan untuk tahun <?= esc($selectedYear) ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = ($currentPage - 1) * 10 + 1;
                        foreach ($kolektors as $col): 
                        ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/20 transition-colors duration-200">
                                <td class="py-3 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono">
                                        <?= esc($col['tahun']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-300 font-mono text-xs"><?= esc($col['kd_kolektor']) ?></td>
                                <td class="py-3 px-4 text-white font-bold"><?= esc($col['nm_kolektor']) ?></td>
                                <td class="py-3 px-4 text-slate-300">
                                    <span class="font-semibold text-slate-200"><?= esc($col['nm_desa']) ?></span> 
                                    <span class="text-xs text-slate-500">(<?= esc($col['nm_kecamatan']) ?>)</span>
                                </td>
                                <td class="py-3 px-4 text-slate-400 text-xs"><?= $col['dusun'] ? esc($col['dusun']) : '<span class="text-slate-600">-</span>' ?></td>
                                <td class="py-3 px-4 text-slate-300 font-mono text-xs"><?= $col['norek_kolektor'] ? esc($col['norek_kolektor']) : '<span class="text-slate-600">-</span>' ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Edit button -->
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($col)) ?>)" 
                                                class="h-8 w-8 rounded-lg bg-indigo-500/10 hover:bg-indigo-500 text-indigo-400 hover:text-white flex items-center justify-center border border-indigo-500/20 transition-all cursor-pointer"
                                                title="Edit Kolektor">
                                            <i class="fa-solid fa-pencil text-xs"></i>
                                        </button>
                                        <!-- Delete button -->
                                        <button onclick="confirmDelete(<?= $col['kolektor_id'] ?>, '<?= esc($col['nm_kolektor']) ?>')" 
                                                class="h-8 w-8 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white flex items-center justify-center border border-rose-500/20 transition-all cursor-pointer"
                                                title="Hapus Kolektor">
                                            <i class="fa-solid fa-trash text-xs"></i>
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
                    Menampilkan halaman <span class="font-bold text-slate-200"><?= $currentPage ?></span> dari <span class="font-bold text-slate-200"><?= $totalPages ?></span> (<span class="font-semibold"><?= $totalItems ?></span> kolektor pada tahun <?= esc($selectedYear) ?>)
                </span>
                <div class="flex items-center gap-1">
                    <!-- Prev Button -->
                    <a href="<?= base_url('admin/kolektor?page=' . ($currentPage - 1) . '&tahun=' . $selectedYear . ($search ? '&search=' . urlencode($search) : '') . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($selectedDesa ? '&desa_id=' . $selectedDesa : '')) ?>" 
                       class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                        <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                    </a>
                    
                    <!-- Page Numbers -->
                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)): ?>
                            <a href="<?= base_url('admin/kolektor?page=' . $i . '&tahun=' . $selectedYear . ($search ? '&search=' . urlencode($search) : '') . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($selectedDesa ? '&desa_id=' . $selectedDesa : '')) ?>" 
                               class="h-8 w-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all border <?= $i === $currentPage ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-white' ?>">
                                <?= $i ?>
                            </a>
                        <?php elseif ($i == 2 || $i == $totalPages - 1): ?>
                            <span class="text-slate-600 px-1 text-xs">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <a href="<?= base_url('admin/kolektor?page=' . ($currentPage + 1) . '&tahun=' . $selectedYear . ($search ? '&search=' . urlencode($search) : '') . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($selectedDesa ? '&desa_id=' . $selectedDesa : '')) ?>" 
                       class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>">
                        Next <i class="fa-solid fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- Modal Form Add/Edit Collector -->
<div id="form-modal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="w-full max-w-lg glass-panel rounded-3xl shadow-2xl overflow-hidden relative flex flex-col max-h-[90vh] my-auto">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Modal Header (Fixed at top) -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-slate-900/80 backdrop-blur flex-shrink-0 z-10">
            <h3 id="modal-title" class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-indigo-400" id="modal-icon"></i>
                <span>Tambah Kolektor Baru</span>
            </h3>
            <button type="button" onclick="closeFormModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Form (Flex column with scrollable body and pinned footer) -->
        <form action="<?= base_url('admin/kolektor/save') ?>" method="POST" class="flex flex-col flex-grow overflow-hidden m-0">
            <?= csrf_field() ?>
            <input type="hidden" id="form-kolektor-id" name="kolektor_id" value="">

            <!-- Scrollable Content Body -->
            <div class="p-6 space-y-4 overflow-y-auto custom-scrollbar flex-grow">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <!-- Input Tahun -->
                    <div class="space-y-1.5">
                        <label for="form-tahun" class="text-xs font-semibold text-slate-300">Tahun <span class="text-rose-500">*</span></label>
                        <input type="number" id="form-tahun" name="tahun" required min="2020" max="2099" value="<?= $selectedYear ?>"
                               class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold font-mono">
                    </div>

                    <!-- Dropdown Pilih Kecamatan -->
                    <div class="space-y-1.5">
                        <label for="form-kec" class="text-xs font-semibold text-slate-300">Pilih Kecamatan</label>
                        <select id="form-kec" class="w-full select2-el" style="width: 100%;">
                            <option value="">-- Pilih Kecamatan --</option>
                            <?php foreach ($kecamatans as $kec): ?>
                                <option value="<?= $kec['kecamatan_id'] ?>"><?= esc($kec['nm_kecamatan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dropdown Pilih Desa (dependent) -->
                    <div class="space-y-1.5">
                        <label for="form-desa" class="text-xs font-semibold text-slate-300">Pilih Desa <span class="text-rose-500">*</span></label>
                        <select id="form-desa" name="desa_id" class="w-full select2-el" style="width: 100%;" disabled>
                            <option value="">-- Pilih Desa --</option>
                        </select>
                    </div>
                </div>

                <!-- Kode Kolektor -->
                <div class="space-y-1.5">
                    <label for="form-kode" class="text-xs font-semibold text-slate-300">Kode Kolektor (2 digit angka) <span class="text-rose-500">*</span></label>
                    <input type="text" id="form-kode" name="kd_kolektor" required maxlength="2" placeholder="Contoh: 01" pattern="[0-9]{2}"
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-mono">
                    <span id="kode-feedback" class="text-xs font-medium hidden"></span>
                </div>

                <!-- Nama Kolektor -->
                <div class="space-y-1.5">
                    <label for="form-nama" class="text-xs font-semibold text-slate-300">Nama Kolektor <span class="text-rose-500">*</span></label>
                    <input type="text" id="form-nama" name="nm_kolektor" required placeholder="Masukkan nama lengkap kolektor"
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                </div>

                <!-- Dusun (Optional) -->
                <div class="space-y-1.5">
                    <label for="form-dusun" class="text-xs font-semibold text-slate-300">Dusun (Opsional)</label>
                    <input type="text" id="form-dusun" name="dusun" placeholder="Masukkan nama dusun jika ada"
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                </div>

                <!-- Nomor Rekening (Optional) -->
                <div class="space-y-1.5">
                    <label for="form-norek" class="text-xs font-semibold text-slate-300">Nomor Rekening (Opsional)</label>
                    <input type="text" id="form-norek" name="norek_kolektor" placeholder="Masukkan nomor rekening kolektor jika ada"
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-mono">
                </div>
            </div>

            <!-- Action buttons (Fixed footer at bottom) -->
            <div class="flex justify-end gap-2.5 border-t border-slate-800 bg-slate-900/90 backdrop-blur px-6 py-4 flex-shrink-0 z-10">
                <button type="button" onclick="closeFormModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="form-submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Salin Kolektor Antar Tahun -->
<div id="copy-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md glass-panel rounded-3xl shadow-2xl overflow-hidden relative">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-copy text-indigo-400"></i>
                <span>Salin Kolektor Antar Tahun</span>
            </h3>
            <button type="button" onclick="closeCopyModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= base_url('admin/kolektor/copy') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <p class="text-xs text-slate-400 leading-relaxed">
                Salin daftar kolektor dari tahun sebelumnya sebagai data awal untuk tahun berikutnya. Kolektor yang kodenya sudah ada di tahun tujuan tidak akan ditimpa.
            </p>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="copy-from-year" class="text-xs font-semibold text-slate-300">Tahun Asal</label>
                    <select id="copy-from-year" name="from_year" required class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold cursor-pointer">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label for="copy-to-year" class="text-xs font-semibold text-slate-300">Tahun Tujuan</label>
                    <input type="number" id="copy-to-year" name="to_year" required min="2020" max="2099" value="<?= $selectedYear + 1 ?>"
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold font-mono">
                </div>
            </div>

            <div class="flex justify-end gap-2.5 border-t border-slate-800/80 pt-4 mt-4">
                <button type="button" onclick="closeCopyModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Mulai Salin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Confirmation Form -->
<form id="delete-form" action="<?= base_url('admin/kolektor/delete') ?>" method="POST" class="hidden">
    <?= csrf_field() ?>
    <input type="hidden" id="delete-kolektor-id" name="kolektor_id" value="">
    <input type="hidden" name="redirect_tahun" value="<?= $selectedYear ?>">
</form>

<!-- Modal Hapus Dialog Box -->
<div id="delete-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md glass-panel rounded-3xl p-6 shadow-2xl relative text-center space-y-4">
        <div class="h-14 w-14 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-2xl mx-auto border border-rose-500/20">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-base font-bold text-white">Konfirmasi Hapus</h3>
            <p class="text-xs text-slate-400">Apakah Anda yakin ingin menghapus data kolektor <span id="delete-col-name" class="font-bold text-rose-400"></span>? Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="flex justify-center gap-2 pt-2">
            <button onclick="closeDeleteModal()" class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-bold transition-all cursor-pointer">
                Batal
            </button>
            <button onclick="submitDelete()" class="py-2.5 px-5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition-all cursor-pointer">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Include jQuery first, then Select2 JS via CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const formModal = document.getElementById('form-modal');
    const copyModal = document.getElementById('copy-modal');
    const deleteModal = document.getElementById('delete-modal');
    
    const modalTitle = document.getElementById('modal-title');
    const modalIcon = document.getElementById('modal-icon');
    
    const formKolektorId = document.getElementById('form-kolektor-id');
    const formTahunInput = document.getElementById('form-tahun');
    const formKecSelect = document.getElementById('form-kec');
    const formDesaSelect = document.getElementById('form-desa');
    const formKodeInput = document.getElementById('form-kode');
    const formNamaInput = document.getElementById('form-nama');
    const formDusunInput = document.getElementById('form-dusun');
    const formNorekInput = document.getElementById('form-norek');
    const kodeFeedback = document.getElementById('kode-feedback');
    const formSubmitBtn = document.getElementById('form-submit');
    
    const deleteForm = document.getElementById('delete-form');
    const deleteKolektorId = document.getElementById('delete-kolektor-id');
    const deleteColNameSpan = document.getElementById('delete-col-name');

    // Initialize Select2 dropdown
    $(document).ready(function() {
        $('.select2-el').select2({
            dropdownParent: $('#form-modal'),
            width: '100%'
        });

        // Helper: Dynamic filtering of Desas based on selected Kecamatan inside modal
        $('#form-kec').on('change', async function() {
            const kecId = $(this).val();
            await loadDesasForModal(kecId);
            await checkDuplicateCode();
        });

        $('#form-desa').on('change', async function() {
            await checkDuplicateCode();
        });

        // Form submit validation for desa_id
        $('#form-modal form').on('submit', function(e) {
            const desaId = $('#form-desa').val();
            if (!desaId) {
                e.preventDefault();
                alert('Silakan pilih Desa terlebih dahulu.');
                $('#form-desa').select2('open');
                return false;
            }
        });
    });

    async function loadDesasForModal(kecId, selectedDesaId = null) {
        const $desa = $('#form-desa');
        $desa.empty();
        $desa.append(new Option('-- Pilih Desa --', '', true, !selectedDesaId));

        if (!kecId) {
            $desa.prop('disabled', true);
            $desa.val('').trigger('change.select2');
            return;
        }
        $desa.prop('disabled', false);
        try {
            const response = await fetch(`<?= base_url('admin/kolektor/get-desas') ?>?kecamatan_id=${kecId}`);
            if (!response.ok) throw new Error();
            const desas = await response.json();
            desas.forEach(d => {
                const isSelected = (selectedDesaId && String(d.desa_id) === String(selectedDesaId));
                $desa.append(new Option(d.nm_desa, d.desa_id, false, isSelected));
            });
            $desa.val(selectedDesaId || '').trigger('change.select2');
        } catch(e) {
            console.error('Error fetching desas for modal', e);
        }
    }

    async function checkDuplicateCode() {
        const desaId = formDesaSelect.value;
        const tahun = formTahunInput.value;
        const kdKolektor = formKodeInput.value;
        const kolektorId = formKolektorId.value;

        // Reset feedback first
        kodeFeedback.classList.add('hidden');
        kodeFeedback.textContent = '';
        kodeFeedback.className = 'text-xs font-medium hidden';
        formSubmitBtn.disabled = false;
        formSubmitBtn.classList.remove('opacity-50', 'cursor-not-allowed');

        if (!desaId || !tahun || kdKolektor.length !== 2 || !/^\d+$/.test(kdKolektor)) {
            return;
        }

        try {
            const url = `<?= base_url('admin/kolektor/check-duplicate') ?>?desa_id=${desaId}&tahun=${tahun}&kd_kolektor=${kdKolektor}&kolektor_id=${kolektorId}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error();
            const result = await response.json();

            if (result.duplicate) {
                kodeFeedback.textContent = `Kode Kolektor "${kdKolektor}" sudah terdaftar untuk Desa ini pada tahun ${tahun}.`;
                kodeFeedback.className = 'text-xs font-medium text-rose-400 block mt-1';
                kodeFeedback.classList.remove('hidden');
                formSubmitBtn.disabled = true;
                formSubmitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                kodeFeedback.textContent = 'Kode Kolektor tersedia.';
                kodeFeedback.className = 'text-xs font-medium text-emerald-400 block mt-1';
                kodeFeedback.classList.remove('hidden');
            }
        } catch (e) {
            console.error('Error checking duplicate collector code:', e);
        }
    }

    // Attach event listeners for real-time validation check
    formKodeInput.addEventListener('input', checkDuplicateCode);
    formTahunInput.addEventListener('input', checkDuplicateCode);
    formDesaSelect.addEventListener('change', checkDuplicateCode);

    function openAddModal() {
        formKolektorId.value = '';
        formTahunInput.value = '<?= $selectedYear ?>';
        $('#form-kec').val('').trigger('change.select2');
        
        const $desa = $('#form-desa');
        $desa.empty();
        $desa.append(new Option('-- Pilih Desa --', '', true, true));
        $desa.prop('disabled', true);
        $desa.val('').trigger('change.select2');

        formKodeInput.value = '';
        formNamaInput.value = '';
        formDusunInput.value = '';
        formNorekInput.value = '';
        
        // Reset feedback
        kodeFeedback.classList.add('hidden');
        kodeFeedback.textContent = '';
        formSubmitBtn.disabled = false;
        formSubmitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        
        modalTitle.querySelector('span').textContent = 'Tambah Kolektor Baru';
        modalIcon.className = 'fa-solid fa-user-plus text-indigo-400';
        
        formModal.classList.remove('hidden');
    }

    async function openEditModal(colData) {
        formKolektorId.value = colData.kolektor_id;
        formTahunInput.value = colData.tahun || '<?= $selectedYear ?>';
        formKodeInput.value = colData.kd_kolektor;
        formNamaInput.value = colData.nm_kolektor;
        formDusunInput.value = colData.dusun || '';
        formNorekInput.value = colData.norek_kolektor || '';
        
        // Reset feedback
        kodeFeedback.classList.add('hidden');
        kodeFeedback.textContent = '';
        formSubmitBtn.disabled = false;
        formSubmitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        
        modalTitle.querySelector('span').textContent = 'Edit Data Kolektor';
        modalIcon.className = 'fa-solid fa-pencil text-indigo-400';
        
        formModal.classList.remove('hidden');
        
        if (colData.kecamatan_id) {
            $('#form-kec').val(colData.kecamatan_id).trigger('change.select2');
            await loadDesasForModal(colData.kecamatan_id, colData.desa_id);
            await checkDuplicateCode();
        } else {
            $('#form-kec').val('').trigger('change.select2');
            const $desa = $('#form-desa');
            $desa.empty().append(new Option('-- Pilih Desa --', '', true, true)).prop('disabled', true).trigger('change.select2');
        }
    }

    function closeFormModal() {
        formModal.classList.add('hidden');
    }

    function openCopyModal() {
        copyModal.classList.remove('hidden');
    }

    function closeCopyModal() {
        copyModal.classList.add('hidden');
    }

    function confirmDelete(id, name) {
        deleteKolektorId.value = id;
        deleteColNameSpan.textContent = name;
        deleteModal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }

    function submitDelete() {
        deleteForm.submit();
    }
</script>
<?= $this->endSection() ?>
