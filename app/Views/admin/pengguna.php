<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Manajemen Akun Pengguna<?= $this->endSection() ?>

<?= $this->section('page_header') ?>Manajemen Akun Pengguna<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Control Header -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            
            <!-- Search Form -->
            <form method="GET" action="<?= base_url('admin/pengguna') ?>" class="flex items-center gap-2 max-w-md w-full">
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Cari username..." 
                           class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-9 pr-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                </div>
                <button type="submit" class="py-2.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all cursor-pointer">
                    Cari
                </button>
                <?php if ($search): ?>
                    <a href="<?= base_url('admin/pengguna') ?>" class="py-2.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                        Reset
                    </a>
                <?php endif; ?>
            </form>

            <!-- Add User Button -->
            <div>
                <button onclick="openAddModal()" 
                        class="w-full sm:w-auto py-2.5 px-5 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/35 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Tambah Pengguna</span>
                </button>
            </div>

        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-panel rounded-3xl p-6 shadow-lg space-y-4">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm border-collapse min-w-[600px]">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3.5 px-4 w-16 text-center">No</th>
                        <th class="py-3.5 px-4">Username</th>
                        <th class="py-3.5 px-4 w-40">Role Akses</th>
                        <th class="py-3.5 px-4 w-48 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500 font-medium">
                                <i class="fa-solid fa-user-shield text-2xl block mb-2 text-slate-600"></i>
                                Tidak ada akun pengguna terdaftar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($users as $u): 
                        ?>
                            <tr class="border-b border-slate-800/60 hover:bg-slate-800/20 transition-colors duration-200">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                <td class="py-3.5 px-4 text-white font-bold flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-xs uppercase">
                                        <?= substr($u['username'], 0, 2) ?>
                                    </div>
                                    <span><?= esc($u['username']) ?></span>
                                    <?php if ($u['user_id'] == session()->get('user_id')): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/25">Akun Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase tracking-wider text-[10px]">
                                            Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700 uppercase tracking-wider text-[10px]">
                                            User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Reset Password Button -->
                                        <button onclick="openResetModal(<?= $u['user_id'] ?>, '<?= esc($u['username']) ?>')" 
                                                class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-bold transition-all cursor-pointer flex items-center gap-1">
                                            <i class="fa-solid fa-key text-[10px] text-amber-400"></i>
                                            <span>Reset Pass</span>
                                        </button>
                                        
                                        <!-- Delete User Button (only if not self) -->
                                        <?php if ($u['user_id'] != session()->get('user_id')): ?>
                                            <button onclick="confirmDelete(<?= $u['user_id'] ?>, '<?= esc($u['username']) ?>')" 
                                                    class="h-8 w-8 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white flex items-center justify-center border border-rose-500/20 transition-all cursor-pointer"
                                                    title="Hapus Akun">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        <?php else: ?>
                                            <div class="w-8"></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Form Tambah Pengguna -->
<div id="add-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md glass-panel rounded-3xl shadow-2xl overflow-hidden relative">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-indigo-400"></i>
                <span>Tambah Pengguna Baru</span>
            </h3>
            <button type="button" onclick="closeAddModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form action="<?= base_url('admin/pengguna/save') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Username -->
            <div class="space-y-1.5">
                <label for="form-username" class="text-xs font-semibold text-slate-300">Username <span class="text-rose-500">*</span></label>
                <input type="text" id="form-username" name="username" required placeholder="Masukkan username unik"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="form-password" class="text-xs font-semibold text-slate-300">Password <span class="text-rose-500">*</span></label>
                <input type="password" id="form-password" name="password" required placeholder="••••••••" minlength="4"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Role select -->
            <div class="space-y-1.5">
                <label for="form-role" class="text-xs font-semibold text-slate-300">Role Akses <span class="text-rose-500">*</span></label>
                <select id="form-role" name="role" required class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2.5 border-t border-slate-800/80 pt-4 mt-6">
                <button type="button" onclick="closeAddModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Tambah Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="reset-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md glass-panel rounded-3xl shadow-2xl overflow-hidden relative">
        <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800 bg-slate-900/50">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-key text-indigo-400"></i>
                <span>Reset Password Pengguna</span>
            </h3>
            <button type="button" onclick="closeResetModal()" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form action="<?= base_url('admin/pengguna/reset') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="reset-user-id" name="user_id" value="">

            <div class="rounded-2xl bg-slate-900/50 border border-slate-800 p-4 text-xs text-slate-400">
                Meng-override password untuk pengguna: <span id="reset-username-text" class="font-bold text-white"></span>
            </div>

            <!-- New Password -->
            <div class="space-y-1.5">
                <label for="reset-password" class="text-xs font-semibold text-slate-300">Password Baru <span class="text-rose-500">*</span></label>
                <input type="password" id="reset-password" name="password" required placeholder="••••••••" minlength="4"
                       class="w-full rounded-2xl bg-slate-900 border border-slate-700 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2.5 border-t border-slate-800/80 pt-4 mt-6">
                <button type="button" onclick="closeResetModal()" class="py-2.5 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-sm font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20 transition-all cursor-pointer">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form Hapus User -->
<form id="delete-form" action="<?= base_url('admin/pengguna/delete') ?>" method="POST" class="hidden">
    <?= csrf_field() ?>
    <input type="hidden" id="delete-user-id" name="user_id" value="">
</form>

<!-- Modal Hapus Dialog Box -->
<div id="delete-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md glass-panel rounded-3xl p-6 shadow-2xl relative text-center space-y-4">
        <div class="h-14 w-14 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-2xl mx-auto border border-rose-500/20">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-base font-bold text-white">Konfirmasi Hapus Pengguna</h3>
            <p class="text-xs text-slate-400">Apakah Anda yakin ingin menghapus akun pengguna <span id="delete-username-text" class="font-bold text-rose-400"></span>? Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="flex justify-center gap-2 pt-2">
            <button onclick="closeDeleteModal()" class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-bold transition-all">
                Batal
            </button>
            <button onclick="submitDelete()" class="py-2.5 px-5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition-all">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const addModal = document.getElementById('add-modal');
    const resetModal = document.getElementById('reset-modal');
    const deleteModal = document.getElementById('delete-modal');
    
    const formUsernameInput = document.getElementById('form-username');
    const formPasswordInput = document.getElementById('form-password');
    const formRoleSelect = document.getElementById('form-role');
    
    const resetUserIdInput = document.getElementById('reset-user-id');
    const resetUsernameTextSpan = document.getElementById('reset-username-text');
    const resetPasswordInput = document.getElementById('reset-password');
    
    const deleteForm = document.getElementById('delete-form');
    const deleteUserIdInput = document.getElementById('delete-user-id');
    const deleteUsernameTextSpan = document.getElementById('delete-username-text');

    function openAddModal() {
        formUsernameInput.value = '';
        formPasswordInput.value = '';
        formRoleSelect.value = 'user';
        addModal.classList.remove('hidden');
    }

    function closeAddModal() {
        addModal.classList.add('hidden');
    }

    function openResetModal(userId, username) {
        resetUserIdInput.value = userId;
        resetUsernameTextSpan.textContent = username;
        resetPasswordInput.value = '';
        resetModal.classList.remove('hidden');
    }

    function closeResetModal() {
        resetModal.classList.add('hidden');
    }

    function confirmDelete(userId, username) {
        deleteUserIdInput.value = userId;
        deleteUsernameTextSpan.textContent = username;
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
