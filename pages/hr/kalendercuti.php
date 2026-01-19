<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center pt-3 pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-calendar-heart-fill text-pink-500"></i> Kalender Cuti
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Jadwal cuti karyawan yang telah disetujui.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 items-center">
        <!-- Filter Divisi -->
        <select id="filter-divisi-kalender" class="block w-full sm:w-48 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-primary focus:border-primary sm:text-sm shadow-sm">
            <option value="">Semua Divisi</option>
        </select>

        <div class="flex flex-wrap gap-3 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-1.5"></span> Cuti Tahunan</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-1.5"></span> Cuti Sakit</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-purple-500 mr-1.5"></span> Melahirkan</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-500 mr-1.5"></span> Lainnya</div>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-5">
    <div id="leave-calendar"></div>
</div>

<!-- Modal Detail Cuti -->
<div id="eventDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEventDetailModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title-detail">Detail Cuti</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeEventDetailModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Karyawan</label>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1" id="detail-nama"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Cuti</label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1" id="detail-jenis"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durasi</label>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1" id="detail-durasi"></p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</label>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1" id="detail-tanggal"></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</label>
                    <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-sm text-gray-700 dark:text-gray-300 italic" id="detail-keterangan"></div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn-edit-cuti" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">Edit</button>
                <button type="button" class="w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors" onclick="closeEventDetailModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Edit Cuti (Hidden by default) -->
<div id="editCutiModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('editCutiModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Edit Pengajuan Cuti</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="document.getElementById('editCutiModal').classList.add('hidden')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6">
                <form id="edit-cuti-form">
                    <input type="hidden" name="id" id="edit-cuti-id">
                    <input type="hidden" name="action" value="save">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
                        <!-- Karyawan di-disable saat edit untuk keamanan data absensi -->
                        <select name="karyawan_id" id="edit-karyawan-id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-gray-100 cursor-not-allowed" readonly></select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Cuti</label>
                        <select name="jenis_cuti_id" id="edit-jenis-id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></select>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="edit-tanggal-mulai" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="edit-tanggal-selesai" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                        <textarea name="keterangan" id="edit-keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn-simpan-edit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan Perubahan</button>
                <button type="button" onclick="document.getElementById('editCutiModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>