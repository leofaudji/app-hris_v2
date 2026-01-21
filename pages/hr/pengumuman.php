<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Pengumuman Internal</h1>
        <button onclick="openPengumumanModal()" class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i class="bi bi-plus-lg mr-2"></i>Buat Pengumuman
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dibuat Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pengumuman-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr><td colspan="5" class="text-center py-4">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="pengumumanModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('pengumumanModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="pengumuman-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="pengumuman_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4" id="modal-title">Form Pengumuman</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                        <input type="text" name="judul" id="judul" required class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Isi Pengumuman</label>
                        <textarea name="isi" id="isi" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lampiran (Opsional)</label>
                        <input type="file" name="lampiran_file" id="lampiran_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600">
                        <p id="current-attachment" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="is_published" name="is_published" type="checkbox" class="focus:ring-primary h-4 w-4 text-primary border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_published" class="font-medium text-gray-700 dark:text-gray-300">Publikasikan</label>
                            <p class="text-gray-500 dark:text-gray-400">Jika dicentang, pengumuman akan langsung tampil di portal karyawan.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="closeModal('pengumumanModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
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