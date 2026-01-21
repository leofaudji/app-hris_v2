<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Template KPI</h1>
        <button onclick="openTemplateModal()" class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <i class="bi bi-plus-lg mr-2"></i>Buat Template
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Template</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Keterangan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody id="template-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr><td colspan="3" class="text-center py-4">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Template -->
<div id="templateModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('templateModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="template-form">
                <input type="hidden" name="action" value="save_template">
                <input type="hidden" name="id" id="template_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4" id="modalTitle">Template KPI</h3>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Template</label>
                        <input type="text" name="nama_template" id="nama_template" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                    </div>

                    <div class="mb-2 flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Indikator Penilaian</label>
                        <button type="button" onclick="addIndicatorRow()" class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-gray-700">
                            <i class="bi bi-plus"></i> Tambah
                        </button>
                    </div>
                    <div class="border rounded-md p-2 max-h-60 overflow-y-auto bg-gray-50 dark:bg-gray-700">
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left w-3/4">Indikator</th>
                                    <th class="text-center w-1/6">Bobot (%)</th>
                                    <th class="w-1/12"></th>
                                </tr>
                            </thead>
                            <tbody id="indicators-container">
                                <!-- Rows added via JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-right font-bold pr-2">Total Bobot:</td>
                                    <td class="text-center font-bold" id="total-bobot">0%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="closeModal('templateModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
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