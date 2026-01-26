<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}

// Security check
check_permission('hr_struktur_organisasi', 'menu');
?>

<div class="flex justify-between flex-wrap items-center pt-3 pb-2 mb-3 border-b border-gray-200 dark:border-gray-700">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <i class="bi bi-diagram-3-fill"></i> Struktur Organisasi
    </h1>
    <div class="w-full md:w-64 mt-2 md:mt-0">
        <select id="filter-divisi-struktur" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            <option value="">Semua Divisi</option>
        </select>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <div class="flex justify-between mb-4 gap-2">
        <button id="export-pdf" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium flex items-center"><i class="bi bi-file-earmark-pdf-fill mr-2"></i> Export PDF</button>
        <div class="flex gap-2">
        <button id="zoom-in" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="Zoom In"><i class="bi bi-zoom-in"></i></button>
        <button id="zoom-out" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="Zoom Out"><i class="bi bi-zoom-out"></i></button>
        <button id="zoom-reset" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="Reset Zoom"><i class="bi bi-arrow-counterclockwise"></i></button>
        </div>
    </div>
    
    <div class="w-full h-[70vh] overflow-auto border border-gray-100 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50 relative">
        <div id="orgchart-container" class="origin-top-left transition-transform duration-200 ease-out p-10">
        <div class="text-center p-10 text-gray-500">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
            Memuat struktur organisasi...
        </div>
    </div>
</div>

<!-- Modal Detail Karyawan -->
<div id="employeeDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('employeeDetailModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">Detail Karyawan</h3>
                        <div id="employee-detail-content" class="text-sm text-gray-500 dark:text-gray-400 space-y-3">
                            <!-- Detail content will be injected here -->
                            <div class="animate-pulse flex space-x-4">
                                <div class="rounded-full bg-gray-200 h-12 w-12"></div>
                                <div class="flex-1 space-y-4 py-1">
                                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                    <div class="space-y-2">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700" onclick="document.getElementById('employeeDetailModal').classList.add('hidden')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>