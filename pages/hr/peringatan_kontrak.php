<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Peringatan Kontrak & Dokumen</h1>

    <!-- Alert Section -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
            <i class="bi bi-exclamation-triangle-fill text-yellow-500 mr-2"></i> Kontrak Akan Berakhir (30 Hari)
        </h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tgl Berakhir</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sisa Hari</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="contract-alert-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr><td colspan="5" class="text-center py-4">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Document Management Section -->
    <div>
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                <i class="bi bi-folder-fill text-blue-500 mr-2"></i> Manajemen Dokumen Karyawan
            </h2>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Karyawan</label>
                <select id="doc_karyawan_select" class="w-full md:w-1/3 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">-- Pilih Karyawan --</option>
                </select>
            </div>

            <div id="document-list-container" class="hidden">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-medium text-gray-900 dark:text-white">Daftar Dokumen</h3>
                    <button onclick="openModal('uploadModal')" class="bg-primary hover:bg-primary-600 text-white px-3 py-1.5 rounded text-sm transition-colors">
                        <i class="bi bi-upload mr-1"></i> Upload Dokumen
                    </button>
                </div>
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jenis</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama File</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tgl Upload</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kadaluarsa</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="document-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Rows -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="document-placeholder" class="text-center py-8 text-gray-500">
                Silakan pilih karyawan terlebih dahulu untuk melihat dokumen.
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div id="uploadModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('uploadModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="upload-form">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="karyawan_id" id="upload_karyawan_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload Dokumen</h3>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Dokumen</label>
                        <select name="jenis_dokumen" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                            <option value="KTP">KTP</option>
                            <option value="NPWP">NPWP</option>
                            <option value="KK">Kartu Keluarga</option>
                            <option value="Ijazah">Ijazah</option>
                            <option value="Kontrak Kerja">Kontrak Kerja</option>
                            <option value="CV">CV</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File (PDF/JPG/PNG)</label>
                        <input type="file" name="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary-600" required accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Kadaluarsa (Opsional)</label>
                        <input type="date" name="tanggal_kadaluarsa" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Isi jika dokumen memiliki masa berlaku (misal: Kontrak, SIM).</p>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Upload</button>
                    <button type="button" onclick="closeModal('uploadModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
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