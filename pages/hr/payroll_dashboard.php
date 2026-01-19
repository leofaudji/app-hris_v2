<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center pt-3 pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-speedometer text-primary"></i> Dashboard Penggajian
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan data penggajian bulan ini.</p>
    </div>
    <div class="flex items-center gap-2">
        <select id="dashboard-filter-divisi" class="block w-48 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary focus:border-primary sm:text-sm shadow-sm" onchange="loadPayrollDashboardData()">
            <option value="">Semua Divisi</option>
        </select>
        <select id="dashboard-filter-tahun" class="block w-32 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary focus:border-primary sm:text-sm shadow-sm" onchange="loadPayrollDashboardData()">
            <?php
            $currentYear = date('Y');
            for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                echo "<option value='$i'>$i</option>";
            }
            ?>
        </select>
    </div>
</div>

<div id="payroll-dashboard-content" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Cards will be injected here -->
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
    <div class="animate-pulse bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-32"></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Total Gaji per Bulan (Tahun Ini)</h3>
        <div class="relative h-64 w-full">
            <canvas id="payroll-trend-chart"></canvas>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Komposisi Gaji Bulan Terakhir</h3>
        <div class="relative h-64 w-full">
            <canvas id="payroll-composition-chart"></canvas>
        </div>
    </div>
</div>

<!-- Division Comparison Chart -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Perbandingan Total Gaji per Divisi (Tahun Ini)</h3>
    <div class="relative h-80 w-full">
        <canvas id="payroll-division-chart"></canvas>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>
