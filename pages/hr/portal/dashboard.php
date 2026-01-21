<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex justify-between flex-wrap items-center pt-3 pb-2 mb-3 border-b border-gray-200 dark:border-gray-700">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <i class="bi bi-person-workspace"></i> Dashboard Karyawan
    </h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Welcome Card -->
    <div class="md:col-span-3 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Selamat Datang, <span id="portal-nama-karyawan">...</span>!</h2>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Ini adalah ringkasan aktivitas dan informasi Anda.</p>
    </div>

    <!-- Sisa Cuti -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 text-center">
        <div class="text-4xl font-bold text-primary" id="portal-sisa-cuti">...</div>
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Sisa Cuti Tahunan</div>
    </div>

    <!-- Kehadiran Bulan Ini -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 text-center">
        <div class="text-4xl font-bold text-green-500" id="portal-kehadiran">...</div>
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Kehadiran Bulan Ini</div>
    </div>

    <!-- Pengajuan Cuti Pending -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 text-center">
        <div class="text-4xl font-bold text-yellow-500" id="portal-cuti-pending">...</div>
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Pengajuan Cuti Pending</div>
    </div>
</div>

<!-- Pengumuman Section -->
<div class="mt-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
        <i class="bi bi-megaphone-fill text-primary"></i> Pengumuman Terbaru
    </h3>
    <div id="portal-pengumuman-list" class="space-y-4">
        <!-- Placeholder -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 text-center text-gray-500">Memuat pengumuman...</div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>