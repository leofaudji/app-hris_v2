<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center pt-3 pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-database-gear text-primary"></i> Dashboard Master Data HR
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan data master kepegawaian.</p>
    </div>
</div>

<div id="master-dashboard-content" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Cards will be injected here -->
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Distribusi Karyawan per Divisi</h3>
        <div class="relative h-64 w-full">
            <canvas id="divisi-chart"></canvas>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Distribusi Karyawan per Status</h3>
        <div class="relative h-64 w-full">
            <canvas id="status-chart"></canvas>
        </div>
    </div>
</div>

<!-- Expiring Contracts Section -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="bi bi-exclamation-triangle text-yellow-500"></i> Kontrak Akan Berakhir (60 Hari Kedepan)
    </h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Karyawan</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tgl Berakhir</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sisa Waktu</th>
                </tr>
            </thead>
            <tbody id="expiring-contracts-body" class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr><td colspan="3" class="px-4 py-4 text-center text-gray-500">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>
