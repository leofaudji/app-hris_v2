<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex justify-between flex-wrap items-center pt-3 pb-2 mb-3 border-b border-gray-200 dark:border-gray-700">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <i class="bi bi-person-badge-fill"></i> Profil Saya
    </h1>
</div>

<div id="profil-content" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="text-center p-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div><p class="mt-4 text-gray-500">Memuat profil...</p></div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>