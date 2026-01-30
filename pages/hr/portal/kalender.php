<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kalender Tim</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Jadwal cuti rekan satu tim, ulang tahun, dan hari libur.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg px-4 py-2 border border-gray-200 dark:border-gray-700 flex items-center">
            <div class="p-2 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mr-3">
                <i class="bi bi-briefcase text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Sisa Cuti Tahunan</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white" id="calendar-sisa-cuti">...</p>
            </div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-4 sm:p-6">
            <div id="team-calendar" class="min-h-[600px]"></div>
        </div>
    </div>
</div>

<!-- Modal Detail Event -->
<div id="eventDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEventDetailModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="detail-nama">Nama Karyawan</h3>
                        <div class="mt-2 space-y-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Jenis:</span> <span id="detail-jenis">-</span></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Tanggal:</span> <span id="detail-tanggal">-</span></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Durasi:</span> <span id="detail-durasi">-</span></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Keterangan:</span> <span id="detail-keterangan">-</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700" onclick="closeEventDetailModal()">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengajuan Cuti Dari Kalender -->
<div id="applyLeaveModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeApplyLeaveModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="calendar-leave-form" onsubmit="submitCalendarLeave(event)">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Ajukan Cuti</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="cal_jenis_cuti" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Cuti</label>
                            <select id="cal_jenis_cuti" name="jenis_cuti_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">Pilih Jenis</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="cal_tanggal_mulai" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mulai</label>
                                <input type="date" id="cal_tanggal_mulai" name="tanggal_mulai" required class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label for="cal_tanggal_selesai" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selesai</label>
                                <input type="date" id="cal_tanggal_selesai" name="tanggal_selesai" required class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="cal_keterangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                            <textarea id="cal_keterangan" name="keterangan" rows="3" required class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Ajukan</button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700" onclick="closeApplyLeaveModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>