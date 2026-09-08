<?php helper('html'); helper('url'); ?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Capaian Realisasi PBB - Pemerintah Kabupaten</title>
    <meta name="description" content="Sistem Informasi Statistik Capaian Realisasi PBB per Kecamatan">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Premium Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
    
    <!-- Tailwind CSS (compiled stylesheet) -->
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a; /* Slate 900 */
            color: #f1f5f9; /* Slate 100 */
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
        }
        
        .glass-panel {
            background: rgba(30, 41, 59, 0.7); /* Slate 800 with opacity */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .gradient-text {
            background: linear-gradient(135deg, #38bdf8 0%, #6366f1 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Custom Scrollbar for list container */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-800 bg-slate-900/80 backdrop-blur-md">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-4">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-chart-pie text-lg text-white"></i>
                    </div>
                    <div>
                        <span class="text-base font-bold tracking-tight text-white block leading-none">PBB-AR</span>
                        <span class="text-xs text-slate-400 mt-1 block">Sistem Informasi Realisasi PBB</span>
                    </div>
                </div>

                <!-- Navigation Action -->
                <div>
                    <a href="<?= base_url('login') ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-200 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all duration-300 shadow-sm shadow-slate-950/20">
                        <i class="fa-solid fa-right-to-bracket text-xs text-indigo-400"></i>
                        <span>Login</span>
                    </a>
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Hero / Banner Section -->
            <section class="glass-panel rounded-3xl p-6 sm:p-8 relative overflow-hidden shadow-xl">
                <!-- Background decor elements -->
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-16 -bottom-16 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 space-y-4 max-w-3xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                        Publik Dashboard
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        Statistik Capaian Realisasi PBB Tahun <span id="hero-year-text" class="text-indigo-400"><?= esc($selectedYear) ?></span>
                    </h1>
                    <p class="text-base text-slate-300 font-medium">
                        Pantau perkembangan persentase capaian wilayah Anda secara real-time.
                    </p>
                </div>
            </section>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Chart Section: Takes 2 Columns on Desktop -->
                <section class="lg:col-span-2 glass-panel rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col space-y-6">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
                        <div class="space-y-1">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-chart-column text-indigo-400"></i>
                                Grafik Persentase Realisasi per Kecamatan
                            </h2>
                            <p class="text-xs text-slate-400">
                                Visualisasi diagram batang horizontal persentase capaian target
                            </p>
                        </div>
                        
                        <!-- Simple Filter Dropdown -->
                        <div class="flex items-center gap-2.5">
                            <label for="year-filter" class="text-xs font-semibold text-slate-300 whitespace-nowrap">
                                <i class="fa-regular fa-calendar-days text-slate-400 mr-1"></i> Pilih Tahun:
                            </label>
                            <select id="year-filter" class="rounded-xl bg-slate-900 border border-slate-700 px-3 py-1.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 hover:bg-slate-800 transition-all duration-300 cursor-pointer">
                                <?php foreach ($years as $yr): ?>
                                    <option value="<?= esc($yr) ?>" <?= $yr === $selectedYear ? 'selected' : '' ?>>
                                        <?= esc($yr) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Chart Canvas Container -->
                    <div class="relative flex-grow min-h-[380px] w-full flex items-center justify-center">
                        <div id="chart-loader" class="absolute z-10 inset-0 flex items-center justify-center bg-slate-900/30 backdrop-blur-sm rounded-2xl hidden">
                            <div class="flex flex-col items-center gap-3">
                                <i class="fa-solid fa-spinner fa-spin text-3xl text-indigo-400"></i>
                                <span class="text-xs font-semibold text-slate-300">Memuat data...</span>
                            </div>
                        </div>
                        <canvas id="realisasiChart" class="w-full h-full"></canvas>
                    </div>

                    <div class="border-t border-slate-800/80 pt-4 flex items-center justify-between text-xs text-slate-400">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-circle-info text-slate-500"></i>
                            * Catatan: Data disajikan dalam bentuk rasio persentase.
                        </span>
                    </div>

                </section>

                <!-- List/Rank Section: Takes 1 Column on Desktop -->
                <section class="glass-panel rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col space-y-6">
                    <div class="border-b border-slate-800/80 pb-5">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-ranking-star text-amber-400"></i>
                            Daftar Capaian Kecamatan
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Urutan performa target dan rasio realisasi
                        </p>
                    </div>

                    <!-- Ranked List Container -->
                    <div id="rank-list" class="space-y-4 overflow-y-auto max-h-[400px] pr-1 custom-scrollbar">
                        <!-- Dynamic list generated by JS -->
                    </div>
                </section>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 bg-slate-950 py-6 text-sm text-slate-400">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                <div>
                    <span class="font-medium text-slate-300">© 2026 Pemerintah Kabupaten</span>
                    <span class="mx-2 hidden sm:inline text-slate-700">|</span>
                    <span class="block sm:inline mt-1 sm:mt-0 text-slate-400 text-xs">PBB-AR Dashboard</span>
                </div>
                <div class="flex items-center gap-2 text-xs bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Update terakhir: <span class="font-semibold text-slate-300">17 Juli 2026</span></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Chart JS Library -->
    <script src="<?= base_url('assets/js/chart.js') ?>"></script>

    <!-- Dashboard App logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectEl = document.getElementById('year-filter');
            const heroYearText = document.getElementById('hero-year-text');
            const chartLoader = document.getElementById('chart-loader');
            const rankListEl = document.getElementById('rank-list');
            let myChart = null;

            // Initial chart data loaded via CI PHP variables
            const initialData = <?= json_encode($chartData) ?>;

            // Colors configuration (Tailwind HSL / RGB mappings)
            const getColorConfig = (percentage) => {
                if (percentage < 50) {
                    return {
                        bg: 'rgba(244, 63, 94, 0.75)',    // bg-rose-500
                        border: 'rgb(244, 63, 94)',
                        text: 'text-rose-400',
                        bgBadge: 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                    };
                } else if (percentage < 80) {
                    return {
                        bg: 'rgba(245, 158, 11, 0.75)',   // bg-amber-500
                        border: 'rgb(245, 158, 11)',
                        text: 'text-amber-400',
                        bgBadge: 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                    };
                } else {
                    return {
                        bg: 'rgba(16, 185, 129, 0.75)',   // bg-emerald-500
                        border: 'rgb(16, 185, 129)',
                        text: 'text-emerald-400',
                        bgBadge: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                    };
                }
            };

            // Function to render the Chart
            const renderChart = (data) => {
                const ctx = document.getElementById('realisasiChart').getContext('2d');
                
                // Map names and percentages
                const labels = data.map(item => item.nama_kecamatan);
                const values = data.map(item => item.persentase);
                
                // Generate dynamic background and border colors for chart bars based on target percentage
                const backgroundColors = data.map(item => getColorConfig(item.persentase).bg);
                const borderColors = data.map(item => getColorConfig(item.persentase).border);

                if (myChart) {
                    myChart.destroy();
                }

                myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 1.5,
                            borderRadius: 6,
                            borderSkipped: false,
                            barThickness: 20
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                padding: 12,
                                backgroundColor: '#1e293b',
                                titleColor: '#f8fafc',
                                bodyColor: '#cbd5e1',
                                borderColor: 'rgba(255,255,255,0.08)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return ` Capaian: ${context.raw.toFixed(2)}%`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                min: 0,
                                max: 100,
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.04)',
                                    drawBorder: false
                                },
                                ticks: {
                                    color: '#94a3b8',
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        size: 11
                                    },
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: '#cbd5e1',
                                    font: {
                                        family: 'Plus Jakarta Sans',
                                        size: 12,
                                        weight: '600'
                                    }
                                }
                            }
                        }
                    }
                });
            };

            // Function to render the Rank List (Right Section)
            const renderRankList = (data) => {
                rankListEl.innerHTML = '';
                
                if (data.length === 0) {
                    rankListEl.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-12 text-slate-500">
                            <i class="fa-solid fa-folder-open text-2xl mb-2"></i>
                            <span class="text-xs">Tidak ada data untuk tahun ini.</span>
                        </div>
                    `;
                    return;
                }

                // Sort copy of data by percentage descending for ranking representation
                const rankedData = [...data].sort((a, b) => b.persentase - a.persentase);

                rankedData.forEach((item, index) => {
                    const colorCfg = getColorConfig(item.persentase);
                    const rankNum = index + 1;
                    
                    // Rank badge color
                    let rankBadgeClass = 'bg-slate-800 text-slate-400';
                    if (rankNum === 1) rankBadgeClass = 'bg-amber-500/20 text-amber-300 border border-amber-500/30';
                    else if (rankNum === 2) rankBadgeClass = 'bg-slate-300/20 text-slate-300 border border-slate-300/30';
                    else if (rankNum === 3) rankBadgeClass = 'bg-amber-700/20 text-amber-500 border border-amber-700/30';

                    const itemHtml = `
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-900/40 hover:bg-slate-900/80 border border-slate-800/40 hover:border-slate-800 transition-all duration-300 group">
                            
                            <!-- Rank Number -->
                            <div class="flex-shrink-0 h-8 w-8 rounded-xl flex items-center justify-center text-xs font-bold ${rankBadgeClass}">
                                ${rankNum}
                            </div>
                            
                            <!-- Kecamatan details & Mini progress bar -->
                            <div class="flex-grow space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-slate-200 group-hover:text-white transition-colors">
                                        ${item.nama_kecamatan}
                                    </span>
                                    <span class="text-xs font-extrabold ${colorCfg.text}">
                                        ${item.persentase.toFixed(2)}%
                                    </span>
                                </div>
                                
                                <!-- Progress Track -->
                                <div class="w-full h-1.5 bg-slate-950 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-1000 ease-out" 
                                         style="width: ${item.persentase}%; background-color: ${colorCfg.border};">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    rankListEl.insertAdjacentHTML('beforeend', itemHtml);
                });
            };

            // Event listener for year dropdown change
            selectEl.addEventListener('change', async (e) => {
                const year = e.target.value;
                heroYearText.textContent = year;
                
                // Show loader
                chartLoader.classList.remove('hidden');
                
                try {
                    const response = await fetch(`<?= base_url('api/realisasi') ?>?tahun=${year}`);
                    if (!response.ok) {
                        throw new Error('Gagal mengambil data dari server');
                    }
                    const data = await response.json();
                    
                    // Render both visual sections
                    renderChart(data);
                    renderRankList(data);
                } catch (error) {
                    console.error('AJAX Error:', error);
                    // Fallback to empty display or toast message
                } finally {
                    // Hide loader
                    chartLoader.classList.add('hidden');
                }
            });

            // Initial render
            renderChart(initialData);
            renderRankList(initialData);
        });
    </script>
</body>
</html>
