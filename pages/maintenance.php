<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex flex-col items-center justify-center min-h-[60vh] text-center p-6 animate-fade-in-up">
    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-8 rounded-full mb-6 shadow-sm">
        <i class="bi bi-cone-striped text-6xl text-yellow-500"></i>
    </div>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">Sistem Dalam Perbaikan</h1>
    <p class="text-gray-600 dark:text-gray-300 max-w-md mx-auto text-lg mb-8">
        Mohon maaf, saat ini kami sedang melakukan pemeliharaan sistem rutin untuk meningkatkan layanan. Silakan kembali lagi beberapa saat lagi.
    </p>
    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800 max-w-lg mx-auto">
            <div class="flex items-start">
                <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-400 text-xl mr-3 mt-0.5"></i>
                <div class="text-left">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 text-sm">Info Admin</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        Mode Maintenance sedang <strong>AKTIF</strong>. User biasa tidak dapat mengakses sistem. 
                        Anda dapat menonaktifkannya kembali melalui menu Pengaturan.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>