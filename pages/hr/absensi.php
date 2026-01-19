<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center pt-3 pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-calendar-check-fill text-primary"></i> Data Absensi
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola dan monitor data kehadiran karyawan harian.</p>
    </div>
    
    <!-- Global Controls (Filters & Actions) -->
    <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
        <!-- Divisi Filter -->
        <div class="w-full sm:w-48">
            <select id="filter-divisi" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary focus:border-primary sm:text-sm shadow-sm">
                <option value="">Semua Divisi</option>
            </select>
        </div>

        <!-- Periode Filter Group -->
        <div class="flex shadow-sm rounded-lg">
            <select id="filter-bulan" class="block w-full sm:w-32 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg focus:ring-primary focus:border-primary sm:text-sm focus:z-10">
                <?php
                $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                foreach ($months as $index => $month) {
                    $selected = ($index + 1) == date('n') ? 'selected' : '';
                    echo "<option value='" . ($index + 1) . "' $selected>$month</option>";
                }
                ?>
            </select>
            <select id="filter-tahun" class="block w-24 border-l-0 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-lg focus:ring-primary focus:border-primary sm:text-sm focus:z-10">
                <?php
                $currentYear = date('Y');
                for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                    echo "<option value='$i'>$i</option>";
                }
                ?>
            </select>
        </div>

        <button type="button" onclick="loadAbsensi()" class="inline-flex justify-center items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors" title="Refresh Data">
            <i class="bi bi-arrow-clockwise text-lg"></i>
        </button>

        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors" onclick="openAbsensiModal()">
            <i class="bi bi-plus-lg mr-2"></i> Tambah Absensi
        </button>
    </div>
</div>

<!-- Summary Stats Placeholder (Diisi oleh JS) -->
<div id="absensi-summary" class="mb-6">
    <!-- Skeleton Loading untuk Summary -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 animate-pulse">
        <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
        <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
        <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
        <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
        <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
    </div>
</div>

<!-- Chart & Top Employees Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Grafik Kehadiran Harian</h3>
        <div class="relative h-72 w-full">
            <canvas id="absensi-chart"></canvas>
        </div>
    </div>

    <!-- Top Employees Section -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="bi bi-award text-yellow-500"></i> Top Karyawan Rajin
        </h3>
        <div id="top-employees-list" class="space-y-3 overflow-y-auto max-h-72 pr-1">
            <!-- Skeleton Loading -->
            <div class="animate-pulse space-y-3">
                <div class="h-14 bg-gray-100 dark:bg-gray-700 rounded-lg"></div>
                <div class="h-14 bg-gray-100 dark:bg-gray-700 rounded-lg"></div>
                <div class="h-14 bg-gray-100 dark:bg-gray-700 rounded-lg"></div>
            </div>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Table Toolbar -->
    <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Riwayat Kehadiran</h3>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- Status Filter -->
            <div class="w-full sm:w-40">
                <select id="filter-status-absensi" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="">Semua Status</option>
                </select>
            </div>
            <!-- Search -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
                <input type="text" id="search-absensi" class="block w-full pl-10 pr-3 py-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary focus:border-primary sm:text-sm transition-colors" placeholder="Cari Nama atau NIP...">
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Karyawan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Golongan</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jam Kerja</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="absensi-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-2"></div>
                            <p>Memuat data absensi...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex items-center justify-between">
         <span class="text-sm text-gray-500 dark:text-gray-400" id="absensi-info">Menampilkan data terbaru</span>
    </div>
</div>

<!-- Modal Form Absensi -->
<div id="absensiModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeAbsensiModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Form Absensi</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeAbsensiModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="absensi-form">
                    <input type="hidden" name="id" id="absensi-id">
                    <input type="hidden" name="action" id="absensi-action" value="save">
                    
                    <div class="mb-4">
                        <label for="karyawan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
                        <select name="karyawan_id" id="karyawan_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Pilih Karyawan</option>
                            <!-- Opsi karyawan dimuat via JS -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="tanggal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>

                    <div class="mb-4">
                        <label for="golongan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Golongan Absensi</label>
                        <select name="golongan" id="golongan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                            <option value="">Memuat golongan...</option>
                            <!-- Opsi dimuat oleh JS -->
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="jam_masuk" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jam Masuk</label>
                            <input type="time" name="jam_masuk" id="jam_masuk" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm" step="1">
                        </div>
                        <div>
                            <label for="jam_keluar" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jam Keluar</label>
                            <input type="time" name="jam_keluar" id="jam_keluar" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Absensi</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                            <option value="">Memuat status...</option>
                            <!-- Opsi dimuat oleh JS -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="save-absensi-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                <button type="button" onclick="closeAbsensiModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>