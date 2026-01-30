<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="space-y-6">
    <!-- Header & Status Hari Ini -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Absensi Karyawan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400" id="current-date-display">...</p>
            </div>
            <div class="flex gap-3">
                <button id="btn-clock-in" class="hidden px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-lg transform transition hover:scale-105 flex items-center gap-2">
                    <i class="bi bi-box-arrow-in-right text-xl"></i> Absen Masuk
                </button>
                <button id="btn-clock-out" class="hidden px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-lg transform transition hover:scale-105 flex items-center gap-2">
                    <i class="bi bi-box-arrow-left text-xl"></i> Absen Pulang
                </button>
                <div id="status-completed" class="hidden px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg font-semibold border border-gray-300 dark:border-gray-600 flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-green-500"></i> Selesai Hari Ini
                </div>
            </div>
        </div>

        <!-- Info Jam -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Jam Masuk</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1" id="display-jam-masuk">--:--</p>
            </div>
            <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-100 dark:border-orange-800">
                <p class="text-xs text-orange-600 dark:text-orange-400 font-medium uppercase">Jam Pulang</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1" id="display-jam-keluar">--:--</p>
            </div>
        </div>
    </div>

    <!-- Filter Riwayat -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label for="filter-bulan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                <select id="filter-bulan" class="block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <?php
                    $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    foreach ($months as $index => $month) {
                        $selected = ($index + 1) == date('n') ? 'selected' : '';
                        echo "<option value='" . ($index + 1) . "' $selected>$month</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="filter-tahun" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                <select id="filter-tahun" class="block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <?php
                    $currentYear = date('Y');
                    for ($i = $currentYear; $i >= $currentYear - 2; $i--) {
                        echo "<option value='$i'>$i</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="loadHistory()" class="w-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                    <i class="bi bi-filter"></i> Tampilkan
                </button>
            </div>
        </div>
    </div>

    <!-- Riwayat Absensi -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Absensi</h3>
        </div>
        
        <!-- Tampilan Desktop (Tabel) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Jam Masuk</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Jam Pulang</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Metode</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Keterangan</th>
                    </tr>
                </thead>
                <tbody id="absensi-history-body" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr><td colspan="7" class="text-center py-4 text-gray-500">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Tampilan Mobile (Card List) -->
        <div id="absensi-mobile-list" class="md:hidden p-4 space-y-4 bg-gray-50 dark:bg-gray-900">
            <div class="text-center py-4 text-gray-500">Memuat data...</div>
        </div>

    </div>
</div>

<!-- Modal Absen -->
<div id="absenModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity" id="absenModalOverlay"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 text-center" id="modal-absen-title">Absen</h3>
                
                <!-- Tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
                    <button class="w-1/2 py-2 text-center text-sm font-medium text-primary border-b-2 border-primary" id="tab-camera">
                        <i class="bi bi-camera-fill mr-1"></i> Foto Wajah
                    </button>
                    <button class="w-1/2 py-2 text-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" id="tab-qr">
                        <i class="bi bi-qr-code-scan mr-1"></i> Scan QR
                    </button>
                </div>

                <!-- Camera Section -->
                <div id="section-camera" class="text-center">
                    <div class="relative w-full h-64 bg-black rounded-lg overflow-hidden mb-3">
                        <video id="webcam" autoplay playsinline class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]"></video>
                        <canvas id="canvas" class="hidden"></canvas>
                    </div>
                    <button id="btn-take-snapshot" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg flex justify-center items-center gap-2">
                        <i class="bi bi-camera"></i> Ambil Foto & Absen
                    </button>
                </div>

                <!-- QR Section -->
                <div id="section-qr" class="hidden text-center">
                    <div id="qr-reader" class="w-full" style="width: 100%;"></div>
                    <p class="text-sm text-gray-500 mt-2">Arahkan kamera ke QR Code Kantor</p>
                </div>

                <div id="location-info" class="mt-4 text-xs text-center text-gray-500 dark:text-gray-400">
                    <i class="bi bi-geo-alt-fill"></i> Mengambil lokasi...
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn-close-modal" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>