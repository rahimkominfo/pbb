<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Penetapan Target PBB Desa<?= $this->endSection() ?>

<?= $this->section('page_header') ?>Penetapan Target PBB Desa<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Include Select2 CSS via CDN for searchable dropdown -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styling select2 to match dark theme dashboard */
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
        background-color: #1e293b !important; /* Slate 800 */
        border-color: #475569 !important; /* Slate 600 */
        color: #fff !important;
        border-radius: 0.5rem !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important; /* Indigo 600 */
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #312e81 !important; /* Indigo 900 */
        color: #fff !important;
    }
    .select2-results__option {
        color: #cbd5e1 !important; /* Slate 300 */
        font-size: 0.875rem !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Filters and Add Target Button -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            
            <form method="GET" action="<?= base_url('admin/target') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-grow max-w-5xl">
                <!-- Select Tahun -->
                <div class="space-y-1.5">
                    <label for="filter-year" class="text-xs font-semibold text-slate-300">Tahun</label>
                    <select id="filter-year" name="tahun" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <?php foreach ($years as $yr): ?>
                            <option value="<?= $yr ?>" <?= $yr == $selectedYear ? 'selected' : '' ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Select Kecamatan -->
                <div class="space-y-1.5">
                    <label for="filter-kec" class="text-xs font-semibold text-slate-300">Kecamatan</label>
                    <select id="filter-kec" name="kecamatan_id" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php foreach ($kecamatans as $kec): ?>
                            <option value="<?= $kec['kecamatan_id'] ?>" <?= $kec['kecamatan_id'] == $selectedKec ? 'selected' : '' ?>>
                                <?= esc($kec['nm_kecamatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Select Desa -->
                <div class="space-y-1.5">
                    <label for="filter-desa" class="text-xs font-semibold text-slate-300">Desa</label>
                    <select id="filter-desa" name="desa_id" onchange="this.form.submit()" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer" <?= empty($selectedKec) ? 'disabled' : '' ?>>
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
                <div class="flex gap-2">
                    <button type="submit" class="flex-grow py-2.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 active:scale-[0.98] transition-all cursor-pointer">
                        Filter
                    </button>
                    <?php if ($selectedKec || $selectedDesa): ?>
                        <a href="<?= base_url('admin/target?tahun=' . $selectedYear) ?>" class="py-2.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Add Target Button -->
            <div>
                <button onclick="openAddModal()" 
                        class="w-full md:w-auto py-2.5 px-5 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/35 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Set Target Baru</span>
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
                        <th class="py-3.5 px-4">Desa (Kecamatan)</th>
                        <th class="py-3.5 px-4 w-24 text-center">Tahun</th>
                        <th class="py-3.5 px-4 text-center w-28">Jumlah NOP</th>
                        <th class="py-3.5 px-4 text-right">Jumlah Target</th>
                        <th class="py-3.5 px-4 text-right">Realisasi Saat Ini</th>
                        <th class="py-3.5 px-4 w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($targets)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 font-medium">
                                <i class="fa-solid fa-bullseye text-2xl block mb-2 text-slate-600"></i>
                                Tidak ada penetapan target terdaftar untuk kriteria filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = ($currentPage - 1) * 10 + 1;
                        foreach ($targets as $t): 
                        ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/20 transition-colors duration-200">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-white"><?= esc($t['nm_desa']) ?></span>
                                    <span class="text-xs text-slate-500 font-semibold ml-1">(<?= esc($t['nm_kecamatan']) ?>)</span>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-300 font-bold"><?= esc($t['tahun']) ?></td>
                                <td class="py-3.5 px-4 text-center text-slate-300 font-semibold"><?= number_format($t['nop'], 0, ',', '.') ?> NOP</td>
                                <td class="py-3.5 px-4 text-right text-slate-200 font-bold">Rp <?= number_format($t['target'], 0, ',', '.') ?></td>
                                <td class="py-3.5 px-4 text-right text-emerald-400 font-semibold">Rp <?= number_format($t['realisasi'], 0, ',', '.') ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-center">
                                        <button onclick="openEditModal(<?= $t['target_id'] ?>)" 
                                                class="h-8 w-8 rounded-lg bg-indigo-500/10 hover:bg-indigo-500 text-indigo-400 hover:text-white flex items-center justify-center border border-indigo-500/20 transition-all cursor-pointer"
                                                title="Edit Target">
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
                    Menampilkan halaman <span class="font-bold text-slate-200"><?= $currentPage ?></span> dari <span class="font-bold text-slate-200"><?= $totalPages ?></span> (<span class="font-semibold"><?= $totalItems ?></span> target desa)
                </span>
                <div class="flex items-center gap-1">
                    <!-- Prev Button -->
                    <a href="<?= base_url('admin/target?page=' . ($currentPage - 1) . '&tahun=' . $selectedYear . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($selectedDesa ? '&desa_id=' . $selectedDesa : '')) ?>" 
                       class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
                        <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                    </a>
                    
                    <!-- Page Numbers -->
                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)): ?>
                            <a href="<?= base_url('admin/target?page=' . $i . '&tahun=' . $selectedYear . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($selectedDesa ? '&desa_id=' . $selectedDesa : '')) ?>" 
                               class="h-8 w-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all border <?= $i === $currentPage ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-white' ?>">
                                <?= $i ?>
                            </a>
                        <?php elseif ($i == 2 || $i == $totalPages - 1): ?>
                            <span class="text-slate-600 px-1 text-xs">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <a href="<?= base_url('admin/target?page=' . ($currentPage + 1) . '&tahun=' . $selectedYear . ($selectedKec ? '&kecamatan_id=' . $selectedKec : '') . ($selectedDesa ? '&desa_id=' . $selectedDesa : '')) ?>" 
                       class="px-3.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition-all <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-40' : '' ?>">
                        Next <i class="fa-solid fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- Modal Form Set Target Baru -->
<div id="target-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg glass-panel rounded-3xl shadow-2xl overflow-hidden relative">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-900/50">
            <h3 id="modal-title" class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-indigo-400"></i>
                <span id="modal-title-text">Penetapan Target Baru</span>
            </h3>
            <button type="button" onclick="closeTargetModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form action="<?= base_url('admin/target/save') ?>" method="POST" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" id="form-target-id" name="target_id" value="">

            <!-- Pilih Tahun -->
            <div class="space-y-1.5" id="form-year-container">
                <label for="form-year" class="text-xs font-semibold text-slate-300">Pilih Tahun <span class="text-rose-500">*</span></label>
                <select id="form-year" name="tahun" required class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                    <?php 
                    // Tampilkan beberapa opsi tahun ke depan
                    $currentYear = (int)date('Y');
                    for($y=$currentYear+1; $y>=$currentYear-5; $y--): 
                    ?>
                        <option value="<?= $y ?>" <?= $y === $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Pilih Kecamatan (untuk menyaring desa) -->
            <div class="space-y-1.5" id="form-kec-container">
                <label for="form-modal-kec" class="text-xs font-semibold text-slate-300">Pilih Kecamatan</label>
                <select id="form-modal-kec" class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                    <option value="">-- Pilih Kecamatan --</option>
                    <?php foreach ($kecamatans as $kec): ?>
                        <option value="<?= $kec['kecamatan_id'] ?>"><?= esc($kec['nm_kecamatan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pilih Desa (searchable Select2 dropdown) -->
            <div class="space-y-1.5" id="form-desa-container">
                <label for="form-desa" class="text-xs font-semibold text-slate-300">Pilih Desa <span class="text-rose-500">*</span></label>
                <select id="form-desa" name="desa_id" required class="w-full select2-el" style="width: 100%;" disabled>
                    <option value="">-- Pilih Desa --</option>
                </select>
            </div>

            <!-- Readonly info when edit -->
            <div id="edit-info-container" class="hidden rounded-2xl bg-slate-900/50 border border-slate-800 p-4 text-xs space-y-1 text-slate-400">
                <p>Tahun Target: <span id="info-year" class="font-bold text-white"></span></p>
                <p>Desa Target: <span id="info-desa" class="font-bold text-white"></span></p>
            </div>

            <!-- Jumlah NOP -->
            <div class="space-y-1.5">
                <label for="form-nop" class="text-xs font-semibold text-slate-300">Jumlah NOP <span class="text-rose-500">*</span></label>
                <input type="number" id="form-nop" name="nop" required placeholder="Contoh: 150" min="0"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold">
            </div>

            <!-- Jumlah Target -->
            <div class="space-y-1.5">
                <label for="form-nominal" class="text-xs font-semibold text-slate-300">Jumlah Target PBB (Rp) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500 text-sm font-semibold">
                        Rp
                    </span>
                    <input type="number" id="form-nominal" name="target" required placeholder="0" min="0"
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-11 pr-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 font-bold">
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2.5 border-t border-slate-800/80 pt-4 mt-6">
                <button type="button" onclick="closeTargetModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Include jQuery first, then Select2 JS via CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const targetModal = document.getElementById('target-modal');
    const modalTitleText = document.getElementById('modal-title-text');
    
    const formTargetId = document.getElementById('form-target-id');
    const formYearSelect = document.getElementById('form-year');
    const formKecSelect = document.getElementById('form-modal-kec');
    const formDesaSelect = document.getElementById('form-desa');
    const formNominalInput = document.getElementById('form-nominal');
    const formNopInput = document.getElementById('form-nop');
    
    const formYearContainer = document.getElementById('form-year-container');
    const formKecContainer = document.getElementById('form-kec-container');
    const formDesaContainer = document.getElementById('form-desa-container');
    const editInfoContainer = document.getElementById('edit-info-container');
    const infoYearSpan = document.getElementById('info-year');
    const infoDesaSpan = document.getElementById('info-desa');

    // Initialize Select2 searchable dropdown
    $(document).ready(function() {
        $('.select2-el').select2({
            dropdownParent: $('#target-modal')
        });
    });

    // Handle kecamatan changes inside modal to filter village list
    formKecSelect.addEventListener('change', async (e) => {
        const kecId = e.target.value;
        await loadDesasForModal(kecId);
    });

    async function loadDesasForModal(kecId, selectedDesaId = null) {
        formDesaSelect.innerHTML = '<option value="">-- Pilih Desa --</option>';
        if (!kecId) {
            formDesaSelect.disabled = true;
            $(formDesaSelect).val('').trigger('change');
            return;
        }
        formDesaSelect.disabled = false;
        try {
            const response = await fetch(`<?= base_url('admin/kolektor/get-desas') ?>?kecamatan_id=${kecId}`);
            if (!response.ok) throw new Error();
            const desas = await response.json();
            desas.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.desa_id;
                opt.textContent = d.nm_desa;
                if (selectedDesaId && d.desa_id == selectedDesaId) {
                    opt.selected = true;
                }
                formDesaSelect.appendChild(opt);
            });
            // Update Select2 UI
            $(formDesaSelect).trigger('change');
        } catch(e) {
            console.error('Error fetching desas for modal', e);
        }
    }

    function openAddModal() {
        formTargetId.value = '';
        formYearSelect.value = '<?= $selectedYear ?>';
        formKecSelect.value = '';
        formDesaSelect.innerHTML = '<option value="">-- Pilih Desa --</option>';
        formDesaSelect.disabled = true;
        $(formDesaSelect).val('').trigger('change');
        formNominalInput.value = '';
        formNopInput.value = '';

        // Show dropdown fields, hide read-only edits info
        formYearContainer.classList.remove('hidden');
        formKecContainer.classList.remove('hidden');
        formDesaContainer.classList.remove('hidden');
        editInfoContainer.classList.add('hidden');

        // Enable inputs
        formYearSelect.disabled = false;
        formDesaSelect.disabled = false;

        modalTitleText.textContent = 'Penetapan Target Baru';
        targetModal.classList.remove('hidden');
    }

    async function openEditModal(targetId) {
        // Fetch target data dynamically from backend via AJAX POST
        try {
            const response = await fetch('<?= base_url('admin/target/edit-fetch') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `target_id=${targetId}`
            });

            if (!response.ok) throw new Error('Gagal mengambil data.');
            const targetData = await response.json();

            // Populate form
            formTargetId.value = targetData.target_id;
            formNominalInput.value = targetData.target;
            formNopInput.value = targetData.nop;

            // Load desas based on target's kecamatan so Select2 selects correctly
            // But we will display a cleaner edit mode by hiding the Year and Desa dropdowns and showing them as plain read-only text,
            // because Year and Desa are the PRIMARY UNIQUE KEY and cannot be edited after target is set.
            formYearSelect.value = targetData.tahun;
            
            // Populate info text
            infoYearSpan.textContent = targetData.tahun;
            
            // Let's resolve the village name
            // Fetch village list to find village name, or we can just fetch it from current page table
            let villageName = '';
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                const editButton = row.querySelector('button[onclick]');
                if (editButton && editButton.getAttribute('onclick').includes(targetId)) {
                    villageName = row.querySelector('.text-white').textContent.trim();
                }
            });
            infoDesaSpan.textContent = villageName || 'Desa ID: ' + targetData.desa_id;

            // Hide dropdown containers, show read-only details container
            formYearContainer.classList.add('hidden');
            formKecContainer.classList.add('hidden');
            formDesaContainer.classList.add('hidden');
            editInfoContainer.classList.remove('hidden');

            // Set select values as backup inside form so they are sent in POST if required
            // (Even though backend will just update the amount based on target_id)
            formYearSelect.disabled = true; // disable to prevent modifications
            formDesaSelect.innerHTML = `<option value="${targetData.desa_id}" selected>Selected</option>`;
            $(formDesaSelect).trigger('change');

            modalTitleText.textContent = 'Edit Jumlah Target';
            targetModal.classList.remove('hidden');

        } catch (err) {
            console.error('AJAX Fetch target failed:', err);
        }
    }

    function closeTargetModal() {
        targetModal.classList.add('hidden');
    }
</script>
<?= $this->endSection() ?>
