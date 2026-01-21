<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard HR</h1>
        
        <!-- Quick Search -->
        <div class="relative w-full md:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="bi bi-search text-gray-400"></i>
            </div>
            <input type="text" id="quick-employee-search" class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm shadow-sm transition-all" placeholder="Cari karyawan..." autocomplete="off">
            <div id="quick-search-results" class="absolute top-full left-0 w-full mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 hidden z-50 max-h-80 overflow-y-auto custom-scrollbar"></div>
        </div>
    </div>

    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Top Performer -->
        <div id="widget-top-performer" class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg p-6 relative overflow-hidden text-white transform hover:scale-[1.02] transition-transform duration-300">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 bg-black opacity-10 rounded-full blur-xl"></div>
            
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Top Performer</p>
                    <h3 class="text-2xl font-bold mt-1" id="top-performer-name">-</h3>
                    <p class="text-sm opacity-90" id="top-performer-jabatan">-</p>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="bi bi-trophy-fill text-3xl text-yellow-300"></i>
                </div>
            </div>
            
            <div class="relative z-10">
                <div id="top-performer-placeholder" class="text-sm italic opacity-70">Memuat data...</div>
                <div id="top-performer-content" class="hidden">
                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-4xl font-bold" id="top-performer-score">0.0</span>
                            <span class="text-sm opacity-80 ml-1">KPI Score</span>
                        </div>
                        <span class="text-xs bg-white/20 px-2 py-1 rounded-lg" id="top-performer-period"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Performer -->
        <div id="widget-bottom-performer" class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg p-6 relative overflow-hidden text-white transform hover:scale-[1.02] transition-transform duration-300">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 bg-black opacity-10 rounded-full blur-xl"></div>

            <div class="relative z-10 flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Perlu Perhatian</p>
                    <h3 class="text-2xl font-bold mt-1" id="bottom-performer-name">-</h3>
                    <p class="text-sm opacity-90" id="bottom-performer-jabatan">-</p>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="bi bi-graph-down-arrow text-3xl text-white"></i>
                </div>
            </div>

            <div class="relative z-10">
                <div id="bottom-performer-placeholder" class="text-sm italic opacity-70">Memuat data...</div>
                <div id="bottom-performer-content" class="hidden">
                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-4xl font-bold" id="bottom-performer-score">0.0</span>
                            <span class="text-sm opacity-80 ml-1">KPI Score</span>
                        </div>
                        <span class="text-xs bg-white/20 px-2 py-1 rounded-lg" id="bottom-performer-period"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Contracts Summary -->
        <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl shadow-lg p-6 relative overflow-hidden text-white transform hover:scale-[1.02] transition-transform duration-300">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 bg-black opacity-10 rounded-full blur-xl"></div>

            <div class="relative z-10 flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider opacity-80">Kontrak Berakhir</p>
                    <p class="text-xs opacity-80">Dalam 30 Hari</p>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="bi bi-exclamation-triangle-fill text-3xl text-white"></i>
                </div>
            </div>

            <div class="relative z-10 mt-4">
                <div class="flex items-end justify-between">
                    <div>
                        <span class="text-5xl font-bold" id="expiring-count">0</span>
                        <span class="text-sm opacity-80 ml-1">Karyawan</span>
                    </div>
                    <a href="#expiring-list-section" class="bg-white text-orange-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-orange-50 transition-colors">
                        Lihat Detail <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <!-- Trend Chart (Wider) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h5 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-graph-up-arrow text-primary"></i> Tren Kinerja
                    </h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Analisis skor KPI 12 bulan terakhir.</p>
                </div>
                <div class="w-64">
                    <select id="kpi_karyawan_select" class="w-full text-sm border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-primary focus:border-primary">
                        <option value="">Pilih Karyawan</option>
                    </select>
                </div>
            </div>
            <div id="kpi-chart-container" class="hidden h-72 w-full"><canvas id="kpi-chart"></canvas></div>
            <div id="kpi-chart-placeholder" class="h-72 flex flex-col items-center justify-center text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-600">
                <i class="bi bi-bar-chart-line text-4xl mb-2 opacity-50"></i>
                <p>Pilih karyawan untuk melihat tren.</p>
            </div>
        </div>

        <!-- Calendar Widget -->
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h5 id="calendar-widget-title" class="text-lg font-bold text-gray-900 dark:text-white">Kalender HR</h5>
                <div class="flex items-center gap-2">
                    <button id="calendar-prev-month" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"><i class="bi bi-chevron-left"></i></button>
                    <button id="calendar-next-month" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div id="calendar-widget-container">
                <!-- Calendar will be rendered here by JS -->
                <div class="text-center py-10 text-gray-400">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary mx-auto"></div>
                    <p class="mt-2 text-sm">Memuat kalender...</p>
                </div>
            </div>
        </div>

        <!-- Expiring Contracts List (Narrower) -->
        <div id="expiring-list-section" class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 flex flex-col">
            <h5 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-clock-history text-amber-500"></i> Detail Kontrak
            </h5>
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar" style="max-h: 300px;">
                <div id="expiring-contracts-list" class="space-y-3">
                    <!-- List items injected here -->
                    <div class="text-center text-gray-500 py-4 text-sm">Memuat data...</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-center">
                <a href="<?= base_url('/hr/peringatan-kontrak') ?>" class="text-sm font-medium text-primary hover:text-primary-600 flex items-center justify-center gap-1">Kelola Semua Dokumen <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>

        <!-- Probation Ending List (New Widget) -->
        <div id="probation-list-section" class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 flex flex-col">
            <h5 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="bi bi-hourglass-split text-blue-500"></i> Masa Percobaan
            </h5>
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar" style="max-h: 300px;">
                <div id="probation-ending-list" class="space-y-3">
                    <div class="text-center text-gray-500 py-4 text-sm">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Chart (Full Width) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h5 class="text-lg font-bold text-gray-900 dark:text-white">Perbandingan Kinerja Tim</h5>
                <p class="text-sm text-gray-500">Analisis skor KPI antar karyawan.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <select id="compare_filter_divisi" class="text-sm border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-primary focus:border-primary">
                    <option value="">Semua Divisi</option>
                </select>
                <select id="compare_filter_bulan" class="text-sm border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-primary focus:border-primary">
                    <?php for($m=1; $m<=12; $m++) echo "<option value='$m' ".($m==date('n')?'selected':'').">".date('F', mktime(0,0,0,$m,1))."</option>"; ?>
                </select>
                <select id="compare_filter_tahun" class="text-sm border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-primary focus:border-primary">
                    <?php for($y=date('Y'); $y>=date('Y')-2; $y--) echo "<option value='$y'>$y</option>"; ?>
                </select>
            </div>
        </div>
        <div id="kpi-comparison-chart-container" class="h-80 w-full">
            <canvas id="kpi-comparison-chart"></canvas>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>