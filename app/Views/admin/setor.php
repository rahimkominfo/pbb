<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Riwayat Input Setoran (Realisasi)<?= $this->endSection() ?>

<?= $this->section('page_header') ?>Riwayat Input Setoran (Realisasi)<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Include Select2 CSS via CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom styles to match dark dashboard */
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

    <!-- Filters & Add button panel -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            
            <!-- Filters -->
            <form method="GET" action="<?= base_url('admin/setor') ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 flex-grow max-w-5xl">
                <!-- Search Kolektor -->
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

                <!-- Filter Bulan -->
                <div class="space-y-1.5">
                    <label for="filter-month" class="text-xs font-semibold text-slate-300">Bulan</label>
                    <select id="filter-month" name="bulan" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="">-- Semua Bulan --</option>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= (string)$num === (string)$selectedMonth ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div class="space-y-1.5">
                    <label for="filter-year" class="text-xs font-semibold text-slate-300">Tahun</label>
                    <select id="filter-year" name="tahun" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="">-- Semua Tahun --</option>
                        <?php foreach ($paymentYears as $y): ?>
                            <option value="<?= $y ?>" <?= (string)$y === (string)$selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action Button inside form -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-grow py-2.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 active:scale-[0.98] transition-all cursor-pointer">
                        Cari
                    </button>
                    <?php if (!empty($search) || (string)$selectedMonth !== (string)$currentMonth || (string)$selectedYear !== (string)$currentYear): ?>
                        <a href="<?= base_url('admin/setor') ?>" class="py-2.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all" title="Reset ke bulan dan tahun sekarang">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>

            </form>

            <!-- Add Setoran Button -->
            <div>
                <button onclick="openSetorModal()" 
                        class="w-full md:w-auto py-2.5 px-5 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/35 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                    <span>Tambah Setoran</span>
                </button>
            </div>

        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3.5 px-4 w-16 text-center">No</th>
                        <th class="py-3.5 px-4 w-36">Tgl Bayar</th>
                        <th class="py-3.5 px-4">Nama Kolektor (Desa)</th>
                        <th class="py-3.5 px-4 w-28 text-center">NOP</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-right">Nominal Setor</th>
                        <th class="py-3.5 px-4 w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($setorans)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 font-medium">
                                <i class="fa-solid fa-receipt text-2xl block mb-2 text-slate-600"></i>
                                Belum ada riwayat setoran terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = ($currentPage - 1) * 10 + 1;
                        foreach ($setorans as $s): 
                        ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/20 transition-colors duration-200">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3.5 px-4 text-slate-300 font-semibold">
                                    <?= date('d-m-Y', strtotime($s['tgl_bayar'])) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-white"><?= esc($s['nm_kolektor']) ?></span>
                                    <span class="text-xs text-indigo-400 font-bold ml-1.5"><?= esc($s['nm_desa']) ?></span>
                                    <?php if (!empty($s['kolektor_tahun'])): ?>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700 ml-1">Th. <?= esc($s['kolektor_tahun']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-300 font-mono text-xs"><?= esc($s['nop']) ?></td>
                                <td class="py-3.5 px-4 text-slate-400 font-semibold text-xs"><?= esc($s['nm_kecamatan']) ?></td>
                                <td class="py-3.5 px-4 text-right text-emerald-400 font-bold">Rp <?= number_format($s['realisasi'], 0, ',', '.') ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick='openEditModal(<?= json_encode([
                                            'realisasi_dsh_id' => $s['realisasi_dsh_id'],
                                            'tgl_bayar' => $s['tgl_bayar'],
                                            'kolektor_id' => $s['kolektor_id'],
                                            'nop' => $s['nop'],
                                            'realisasi' => $s['realisasi']
                                        ]) ?>)' 
                                                class="h-8 w-8 rounded-lg bg-indigo-500/10 hover:bg-indigo-500 text-indigo-400 hover:text-white flex items-center justify-center border border-indigo-500/20 transition-all cursor-pointer"
                                                title="Edit Setoran">
                                            <i class="fa-solid fa-pencil text-xs"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?= $s['realisasi_dsh_id'] ?>)" 
                                                class="h-8 w-8 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white flex items-center justify-center border border-rose-500/20 transition-all cursor-pointer"
                                                title="Hapus Setoran">
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
                    Menampilkan halaman <span class="font-bold text-slate-200"><?= $currentPage ?></span> dari <span class="font-bold text-slate-200"><?= $totalPages ?></span> (<span class="font-semibold"><?= $totalItems ?></span> setoran)
                </span>
                <div class="flex items-center gap-1">
                    <!-- Prev Button -->
                    <a href="<?= base_url('admin/setor?page=' . ($currentPage - 1) . ($search ? '&search=' . urlencode($search) : '') . ($selectedMonth ? '&bulan=' . $selectedMonth : '') . ($selectedYear ? '&tahun=' . $selectedYear : '')) ?>" 
                       class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                        <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                    </a>
                    
                    <!-- Page Numbers -->
                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)): ?>
                            <a href="<?= base_url('admin/setor?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($selectedMonth ? '&bulan=' . $selectedMonth : '') . ($selectedYear ? '&tahun=' . $selectedYear : '')) ?>" 
                               class="h-8 w-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all border <?= $i === $currentPage ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-white' ?>">
                                <?= $i ?>
                            </a>
                        <?php elseif ($i == 2 || $i == $totalPages - 1): ?>
                            <span class="text-slate-600 px-1 text-xs">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <a href="<?= base_url('admin/setor?page=' . ($currentPage + 1) . ($search ? '&search=' . urlencode($search) : '') . ($selectedMonth ? '&bulan=' . $selectedMonth : '') . ($selectedYear ? '&tahun=' . $selectedYear : '')) ?>" 
                       class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>">
                        Next <i class="fa-solid fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- Modal Form Tambah Setoran -->
<div id="setor-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg glass-panel rounded-3xl shadow-2xl overflow-hidden relative">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-indigo-400"></i>
                <span id="modal-title-text">Catat Setoran Baru</span>
            </h3>
            <button type="button" onclick="closeSetorModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form action="<?= base_url('admin/setor/save') ?>" method="POST" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" id="form-setor-id" name="realisasi_dsh_id" value="">

            <!-- Tanggal Bayar -->
            <div class="space-y-1.5">
                <label for="form-tgl" class="text-xs font-semibold text-slate-300">Tanggal Bayar <span class="text-rose-500">*</span></label>
                <input type="date" id="form-tgl" name="tgl_bayar" required value="<?= date('Y-m-d') ?>"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Pilih Kolektor (searchable select dropdown) -->
            <div class="space-y-1.5">
                <label for="form-kolektor" class="text-xs font-semibold text-slate-300">Pilih Kolektor (Nama - Desa) <span class="text-rose-500">*</span></label>
                <select id="form-kolektor" name="kolektor_id" required class="w-full select2-el" style="width: 100%;">
                    <option value="">-- Pilih Kolektor --</option>
                    <?php foreach ($kolektors as $col): ?>
                        <option value="<?= $col['kolektor_id'] ?>">
                            <?= esc($col['nm_kolektor']) ?> - <?= esc($col['nm_desa']) ?> (Th. <?= esc($col['tahun']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- NOP -->
            <div class="space-y-1.5">
                <label for="form-nop" class="text-xs font-semibold text-slate-300">NOP <span class="text-rose-500">*</span></label>
                <input type="number" id="form-nop" name="nop" required placeholder="Masukkan NOP" min="1"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Nominal Setoran -->
            <div class="space-y-1.5">
                <label for="form-nominal" class="text-xs font-semibold text-slate-300">Nominal Realisasi Setor (Rp) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500 text-sm font-semibold">
                        Rp
                    </span>
                    <input type="number" id="form-nominal" name="realisasi" required placeholder="0" min="1"
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-11 pr-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold">
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2.5 border-t border-slate-800/80 pt-4 mt-6">
                <button type="button" onclick="closeSetorModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Simpan Setoran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form Hapus Setoran (hidden) -->
<form id="delete-form" action="<?= base_url('admin/setor/delete') ?>" method="POST" class="hidden">
    <?= csrf_field() ?>
    <input type="hidden" id="delete-setor-id" name="realisasi_dsh_id" value="">
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Include jQuery first, then Select2 JS via CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const setorModal = document.getElementById('setor-modal');
    const modalTitleText = document.getElementById('modal-title-text');
    const formSetorId = document.getElementById('form-setor-id');
    const formKolektorSelect = document.getElementById('form-kolektor');
    const formNominalInput = document.getElementById('form-nominal');
    const formTglInput = document.getElementById('form-tgl');
    const formNopInput = document.getElementById('form-nop');

    // Initialize Select2 dropdown
    $(document).ready(function() {
        $('.select2-el').select2({
            dropdownParent: $('#setor-modal')
        });
    });

    function openSetorModal() {
        formSetorId.value = '';
        formNominalInput.value = '';
        formNopInput.value = '';
        formTglInput.value = '<?= date('Y-m-d') ?>';
        
        // Reset select2 value
        $(formKolektorSelect).val('').trigger('change');
        
        modalTitleText.textContent = 'Catat Setoran Baru';
        setorModal.classList.remove('hidden');
    }

    function openEditModal(data) {
        formSetorId.value = data.realisasi_dsh_id;
        formTglInput.value = data.tgl_bayar;
        formNopInput.value = data.nop;
        formNominalInput.value = data.realisasi;
        
        // Populate select2 and trigger change
        $(formKolektorSelect).val(data.kolektor_id).trigger('change');
        
        modalTitleText.textContent = 'Edit Data Setoran';
        setorModal.classList.remove('hidden');
    }

    function confirmDelete(id) {
        if (confirm("Apakah Anda yakin ingin menghapus catatan setoran ini? Tindakan ini akan memotong nominal dari total realisasi desa terkait.")) {
            document.getElementById('delete-setor-id').value = id;
            document.getElementById('delete-form').submit();
        }
    }

    function closeSetorModal() {
        setorModal.classList.add('hidden');
    }
</script>
<?= $this->endSection() ?>
