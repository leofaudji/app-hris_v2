<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center pt-3 pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-calendar2-week-fill text-primary"></i> Manajemen Cuti
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola pengajuan dan kuota cuti karyawan.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors" onclick="openJatahCutiModal()">
            <i class="bi bi-sliders mr-2"></i> Atur Jatah
        </button>
        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors" onclick="openPengajuanCutiModal()">
            <i class="bi bi-plus-lg mr-2"></i> Buat Pengajuan
        </button>
    </div>
</div>

<!-- Filter Section -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
    <div class="p-5">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <!-- Karyawan Filter -->
            <div class="md:col-span-5">
                <label for="filter-karyawan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Karyawan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-person text-gray-400"></i>
                    </div>
                    <select id="filter-karyawan" class="block w-full pl-10 pr-10 py-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary focus:border-primary sm:text-sm transition-colors">
                        <option value="">Semua Karyawan</option>
                    </select>
                </div>
            </div>

            <!-- Periode Filter -->
            <div class="md:col-span-5">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
                <div class="flex shadow-sm rounded-lg">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-calendar-month text-gray-400"></i>
                        </div>
                        <select id="filter-bulan" class="block w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg focus:ring-primary focus:border-primary sm:text-sm transition-colors">
                            <option value="">Semua Bulan</option>
                            <?php for ($m=1; $m<=12; ++$m) { echo '<option value="'. $m .'">'. date('F', mktime(0, 0, 0, $m, 1)) .'</option>'; } ?>
                        </select>
                    </div>
                    <select id="filter-tahun" class="block w-28 border-l-0 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-r-lg focus:ring-primary focus:border-primary sm:text-sm transition-colors">
                        <?php for ($y=date('Y'); $y>=date('Y')-2; --$y) { echo "<option value='$y'>$y</option>"; } ?>
                    </select>
                </div>
            </div>

            <!-- Action Button -->
            <div class="md:col-span-2">
                <button type="button" id="btn-tampilkan-cuti" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <i class="bi bi-filter mr-2"></i> Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="bi bi-list-ul"></i> Daftar Pengajuan Cuti
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Karyawan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Cuti</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durasi</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="cuti-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-2"></div>
                        <p>Memuat data...</p>
                    </div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Pengajuan Cuti -->
<div id="pengajuanCutiModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closePengajuanCutiModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title-cuti">Form Pengajuan Cuti</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closePengajuanCutiModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6">
                <form id="pengajuan-cuti-form">
                    <input type="hidden" name="id" id="pengajuan-cuti-id">
                    <input type="hidden" name="action" value="save">
                    <div class="mb-4">
                        <label for="cuti-karyawan-id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
                        <select name="karyawan_id" id="cuti-karyawan-id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></select>
                    </div>
                    <div class="mb-4">
                        <label for="cuti-jenis-id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Cuti</label>
                        <select name="jenis_cuti_id" id="cuti-jenis-id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></select>
                        <p id="sisa-cuti-info" class="mt-2 text-xs text-gray-500 dark:text-gray-400 hidden">Sisa Jatah Cuti Tahunan: <span class="font-bold"></span> hari.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="cuti-tanggal-mulai" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="cuti-tanggal-mulai" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label for="cuti-tanggal-selesai" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="cuti-tanggal-selesai" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="cuti-keterangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                        <textarea name="keterangan" id="cuti-keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="save-pengajuan-cuti-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                <button type="button" onclick="closePengajuanCutiModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Jatah Cuti -->
<div id="jatahCutiModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeJatahCutiModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Atur Jatah Cuti Tahunan</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeJatahCutiModal()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6">
                <form id="jatah-cuti-form">
                    <input type="hidden" name="action" value="set_jatah_cuti">
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="set-for-all-karyawan" name="set_for_all" value="1" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Terapkan ke Semua Karyawan Aktif</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Jika dicentang, jatah cuti akan diatur untuk semua karyawan yang berstatus aktif.</p>
                    </div>

                    <div class="mb-4" id="jatah-karyawan-container">
                        <label for="jatah-karyawan-id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
                        <select name="karyawan_id" id="jatah-karyawan-id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <!-- Options loaded by JS -->
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="jatah-tahun" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                            <input type="number" name="tahun" id="jatah-tahun" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="<?= date('Y') ?>">
                        </div>
                        <div>
                            <label for="jatah-awal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Jatah</label>
                            <input type="number" name="jatah_awal" id="jatah-awal" required min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="12">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="save-jatah-cuti-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                <button type="button" onclick="closeJatahCutiModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>