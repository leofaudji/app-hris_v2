<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="space-y-6">
    <!-- Header & Search -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Direktori Karyawan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Temukan dan terhubung dengan rekan kerja Anda.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-funnel text-gray-400"></i>
                    </div>
                    <select id="dir-filter-divisi" class="pl-10 pr-8 py-2.5 w-full sm:w-48 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary focus:border-primary text-sm shadow-sm transition-colors">
                        <option value="">Semua Divisi</option>
                        <!-- Populated by JS -->
                    </select>
                </div>
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-search text-gray-400"></i>
                    </div>
                    <input type="text" id="dir-search-input" class="pl-10 pr-4 py-2.5 w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-primary focus:border-primary text-sm shadow-sm transition-colors" placeholder="Cari nama, jabatan, atau NIP...">
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Container -->
    <div id="directory-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Cards will be injected here -->
        <div class="col-span-full flex flex-col items-center justify-center py-12 text-gray-500">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary mb-3"></div>
            Memuat data...
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>