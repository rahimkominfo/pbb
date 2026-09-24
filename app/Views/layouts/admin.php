<?php helper('html'); helper('url'); ?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Admin Panel PBB-AR</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    
    <!-- Tailwind CSS (compiled stylesheet) -->
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a; /* Slate 900 */
            color: #f1f5f9; /* Slate 100 */
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.03) 0px, transparent 50%);
            background-attachment: fixed;
        }
        
        .glass-panel {
            background: rgba(30, 41, 59, 0.7); /* Slate 800 with opacity */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.01);
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Overall Shell -->
    <div class="flex flex-grow overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 border-r border-slate-800 flex-shrink-0">
            <!-- Sidebar Header -->
            <div class="flex h-16 items-center gap-3 px-6 border-b border-slate-800">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white">
                    <i class="fa-solid fa-chart-pie text-sm"></i>
                </div>
                <div>
                    <span class="text-sm font-bold tracking-tight text-white block">PBB-AR Sinjai</span>
                    <span class="text-[10px] text-slate-500 block">ADMIN PANEL</span>
                </div>
            </div>

            <!-- Sidebar Navigation Menu -->
            <nav class="flex-grow py-6 px-4 space-y-7 overflow-y-auto custom-scrollbar">
                
                <!-- Main Group -->
                <div class="space-y-1.5">
                    <span class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase block mb-2">Utama</span>
                    
                    <a href="<?= base_url('admin/dashboard') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'dashboard' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-chart-line w-4 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Master Data Group -->
                <div class="space-y-1.5">
                    <span class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase block mb-2">Master Data</span>
                    
                    <a href="<?= base_url('admin/kolektor') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'kolektor' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-users w-4 text-center"></i>
                        <span>Master Kolektor</span>
                    </a>

                    <a href="<?= base_url('admin/wilayah') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'wilayah' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-map-location-dot w-4 text-center"></i>
                        <span>Master Wilayah</span>
                    </a>
                </div>

                <!-- Transaksi Group -->
                <div class="space-y-1.5">
                    <span class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase block mb-2">Transaksi</span>
                    
                    <a href="<?= base_url('admin/target') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'target' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-bullseye w-4 text-center"></i>
                        <span>Set Target</span>
                    </a>
                    
                    <a href="<?= base_url('admin/setor') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'setor' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-center"></i>
                        <span>Input Setor</span>
                    </a>
                </div>

                <!-- Laporan & Pengguna Group -->
                <div class="space-y-1.5">
                    <span class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase block mb-2">Sistem</span>
                    
                    <a href="<?= base_url('admin/dashboard') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'dashboard' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                        <span>Laporan</span>
                    </a>

                    <!-- Sub Menu Insentif -->
                    <a href="<?= base_url('admin/insentif') ?>" 
                       class="flex items-center gap-3 pl-9 pr-3 py-2 rounded-xl text-xs font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'insentif' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-gift w-3 text-center"></i>
                        <span>Insentif</span>
                    </a>

                    <!-- Sub Menu Upah Kerja -->
                    <a href="<?= base_url('admin/upah-kerja') ?>" 
                       class="flex items-center gap-3 pl-9 pr-3 py-2 rounded-xl text-xs font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'upahKerja' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-money-bill-wave w-3 text-center"></i>
                        <span>Upah Kerja</span>
                    </a>
                    
                    <a href="<?= base_url('admin/pengguna') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'pengguna' ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                        <i class="fa-solid fa-user-shield w-4 text-center"></i>
                        <span>Pengguna</span>
                    </a>
                </div>

            </nav>

            <!-- Sidebar Footer / Logout -->
            <div class="border-t border-slate-800 p-4 bg-slate-950/40">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-xs uppercase">
                            <?= substr(session()->get('username') ?? 'A', 0, 2) ?>
                        </div>
                        <div class="leading-none">
                            <span class="text-xs font-bold text-white block"><?= esc(session()->get('username')) ?></span>
                            <span class="text-[9px] text-slate-500 uppercase font-semibold block mt-0.5"><?= esc(session()->get('role')) ?></span>
                        </div>
                    </div>
                    <a href="<?= base_url('logout') ?>" class="h-8 w-8 rounded-lg hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 flex items-center justify-center transition-all" title="Logout">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Right Hand Main Container -->
        <div class="flex flex-col flex-grow overflow-hidden">
            
            <!-- Topbar Header -->
            <header class="flex h-16 items-center justify-between gap-4 border-b border-slate-800 bg-slate-900 px-6 flex-shrink-0">
                
                <!-- Toggle Menu for Mobile -->
                <div class="flex items-center gap-3">
                    <button id="mobile-toggle" class="md:hidden h-10 w-10 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl flex items-center justify-center transition-all">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    
                    <h2 class="text-sm font-bold text-slate-200">
                        <?= $this->renderSection('page_header') ?>
                    </h2>
                </div>

                <!-- Page Header Actions (Year filter, etc.) -->
                <div class="flex items-center gap-4">
                    <?= $this->renderSection('header_actions') ?>
                    
                    <!-- Public Link -->
                    <a href="<?= base_url('/') ?>" target="_blank" class="text-xs font-semibold text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-globe text-indigo-400"></i>
                        <span class="hidden sm:inline">Kunjungi Web Publik</span>
                    </a>
                </div>

            </header>

            <!-- Main Content Scroll Container -->
            <main class="flex-grow p-6 overflow-y-auto custom-scrollbar relative">
                
                <!-- Flash Notification Success -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-6 flex items-start gap-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 p-4 text-sm text-emerald-400">
                        <i class="fa-solid fa-circle-check text-base mt-0.5 flex-shrink-0"></i>
                        <div class="flex-grow font-medium"><?= session()->getFlashdata('success') ?></div>
                        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Flash Notification Error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-6 flex items-start gap-3 rounded-2xl bg-rose-500/10 border border-rose-500/25 p-4 text-sm text-rose-400">
                        <i class="fa-solid fa-triangle-exclamation text-base mt-0.5 flex-shrink-0"></i>
                        <div class="flex-grow font-medium"><?= session()->getFlashdata('error') ?></div>
                        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
                
            </main>
        </div>

    </div>

    <!-- Mobile Navigation Drawer Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm hidden transition-opacity duration-300"></div>
    <!-- Mobile Drawer -->
    <div id="mobile-drawer" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden">
        <div class="flex h-16 items-center justify-between gap-3 px-6 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white">
                    <i class="fa-solid fa-chart-pie text-sm"></i>
                </div>
                <span class="text-sm font-bold tracking-tight text-white">PBB-AR Admin</span>
            </div>
            <button id="mobile-close" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <nav class="flex-grow py-6 px-4 space-y-7 overflow-y-auto custom-scrollbar">
            <!-- Mobile Menu -->
            <div class="space-y-1.5">
                <span class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase block mb-2">Menu</span>
                <a href="<?= base_url('admin/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    <i class="fa-solid fa-chart-line w-4 text-center"></i> Dashboard
                </a>
                <a href="<?= base_url('admin/kolektor') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    <i class="fa-solid fa-users w-4 text-center"></i> Master Kolektor
                </a>
                <a href="<?= base_url('admin/target') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    <i class="fa-solid fa-bullseye w-4 text-center"></i> Set Target
                </a>
                <a href="<?= base_url('admin/setor') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    <i class="fa-solid fa-hand-holding-dollar w-4 text-center"></i> Input Setor
                </a>
                <a href="<?= base_url('admin/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i> Laporan
                </a>
                <a href="<?= base_url('admin/insentif') ?>" class="flex items-center gap-3 pl-9 pr-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800">
                    <i class="fa-solid fa-gift w-3 text-center"></i> Insentif
                </a>
                <a href="<?= base_url('admin/upah-kerja') ?>" class="flex items-center gap-3 pl-9 pr-3 py-2 rounded-xl text-xs font-semibold <?= service('router')->controllerName() === '\App\Controllers\Admin' && service('router')->methodName() === 'upahKerja' ? 'text-indigo-400 font-bold bg-indigo-600/10' : 'text-slate-300' ?> hover:bg-slate-800">
                    <i class="fa-solid fa-money-bill-wave w-3 text-center"></i> Upah Kerja
                </a>
                <a href="<?= base_url('admin/pengguna') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    <i class="fa-solid fa-user-shield w-4 text-center"></i> Pengguna
                </a>
            </div>
        </nav>
        <div class="border-t border-slate-800 p-4 bg-slate-950/40">
            <a href="<?= base_url('logout') ?>" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white font-bold text-sm transition-all">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>

    <!-- Script for mobile toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('mobile-toggle');
            const closeBtn = document.getElementById('mobile-close');
            const overlay = document.getElementById('mobile-overlay');
            const drawer = document.getElementById('mobile-drawer');

            const openDrawer = () => {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
                drawer.classList.remove('-translate-x-full');
            };

            const closeDrawer = () => {
                overlay.classList.remove('opacity-100');
                drawer.classList.add('-translate-x-full');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            };

            toggleBtn.addEventListener('click', openDrawer);
            closeBtn.addEventListener('click', closeDrawer);
            overlay.addEventListener('click', closeDrawer);
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
