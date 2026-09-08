<?php helper('html'); helper('url'); ?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Realisasi PBB</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    
    <!-- Tailwind CSS (compiled stylesheet) -->
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a; /* Slate 900 */
            color: #f1f5f9; /* Slate 100 */
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
        }
        
        .glass-panel {
            background: rgba(30, 41, 59, 0.6); /* Slate 800 with opacity */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Login Container -->
    <div class="w-full max-w-md space-y-6">
        
        <!-- Logo & Title -->
        <div class="text-center space-y-2">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 shadow-lg shadow-indigo-500/20">
                <i class="fa-solid fa-chart-pie text-2xl text-white"></i>
            </div>
            <h2 class="text-2xl font-extrabold tracking-tight text-white mt-4">Sistem Informasi PBB-AR</h2>
            <p class="text-sm text-slate-400">Silakan login untuk mengakses panel administrator</p>
        </div>

        <!-- Card Form -->
        <div class="glass-panel rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-5 flex items-start gap-3 rounded-2xl bg-rose-500/10 border border-rose-500/25 p-4 text-sm text-rose-400">
                    <i class="fa-solid fa-triangle-exclamation text-base mt-0.5 flex-shrink-0"></i>
                    <div><?= session()->getFlashdata('error') ?></div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-5 flex items-start gap-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 p-4 text-sm text-emerald-400">
                    <i class="fa-solid fa-circle-check text-base mt-0.5 flex-shrink-0"></i>
                    <div><?= session()->getFlashdata('success') ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                
                <!-- Username field -->
                <div class="space-y-1.5">
                    <label for="username" class="text-xs font-semibold text-slate-300">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fa-solid fa-user text-sm"></i>
                        </span>
                        <input type="text" id="username" name="username" value="<?= old('username') ?>" required placeholder="Masukkan username" 
                               class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-10 pr-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 hover:bg-slate-900/80 transition-all duration-300">
                    </div>
                </div>

                <!-- Password field -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-xs font-semibold text-slate-300">Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" id="password" name="password" required placeholder="••••••••" 
                               class="w-full rounded-2xl bg-slate-900 border border-slate-700 pl-10 pr-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 hover:bg-slate-900/80 transition-all duration-300">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3 px-4 mt-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/35 active:scale-[0.98] transition-all duration-300 cursor-pointer">
                    Sign In
                </button>
            </form>
        </div>

        <!-- Back Link -->
        <div class="text-center">
            <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda Publik
            </a>
        </div>

    </div>
</body>
</html>
