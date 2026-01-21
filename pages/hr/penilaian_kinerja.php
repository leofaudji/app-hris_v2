<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Penilaian Kinerja (KPI)</h1>
        <div class="flex gap-2">
            <select id="filter-bulan" class="rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white text-sm">
                <?php for($m=1; $m<=12; $m++) echo "<option value='$m' ".($m==date('n')?'selected':'').">".date('F', mktime(0,0,0,$m,1))."</option>"; ?>
            </select>
            <select id="filter-tahun" class="rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white text-sm">
                <?php for($y=date('Y'); $y>=date('Y')-2; $y--) echo "<option value='$y'>$y</option>"; ?>
            </select>
            <button onclick="openAppraisalModal()" class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="bi bi-plus-lg mr-2"></i>Nilai Karyawan
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Karyawan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Template</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total Skor</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody id="appraisal-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr><td colspan="5" class="text-center py-4">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Penilaian -->
<div id="appraisalModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('appraisalModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <form id="appraisal-form">
                <input type="hidden" name="action" value="save_appraisal">
                <input type="hidden" name="id" id="appraisal_id">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Form Penilaian Kinerja</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Karyawan</label>
                            <select name="karyawan_id" id="karyawan_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                                <option value="">Pilih Karyawan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template KPI</label>
                            <select name="template_id" id="template_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required onchange="loadTemplateIndicators(this.value)">
                                <option value="">Pilih Template</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
                            <div class="flex gap-2">
                                <select name="periode_bulan" id="form_bulan" class="w-1/2 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                                    <?php for($m=1; $m<=12; $m++) echo "<option value='$m'>".date('F', mktime(0,0,0,$m,1))."</option>"; ?>
                                </select>
                                <select name="periode_tahun" id="form_tahun" class="w-1/2 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                                    <?php for($y=date('Y'); $y>=date('Y')-2; $y--) echo "<option value='$y'>$y</option>"; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Penilaian</label>
                            <input type="date" name="tanggal_penilaian" id="tanggal_penilaian" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="border rounded-md p-3 bg-gray-50 dark:bg-gray-700 mb-4">
                        <h4 class="text-sm font-bold mb-2 text-gray-700 dark:text-gray-300">Detail Penilaian</h4>
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left w-1/3">Indikator</th>
                                    <th class="text-center w-20">Bobot</th>
                                    <th class="text-center w-24">Skor (0-100)</th>
                                    <th class="text-left">Komentar</th>
                                </tr>
                            </thead>
                            <tbody id="appraisal-indicators-body">
                                <tr><td colspan="4" class="text-center py-4 text-gray-500">Pilih template terlebih dahulu.</td></tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-300">
                                    <td colspan="2" class="text-right font-bold pr-2 pt-2">Total Skor Akhir:</td>
                                    <td class="text-center font-bold pt-2 text-lg text-primary" id="final-score">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Umum</label>
                        <textarea name="catatan" id="catatan" rows="2" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"></textarea>
                    </div>
                    
                    <div class="mb-3">
                         <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                         <select name="status" id="status" class="w-full md:w-1/3 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                             <option value="draft">Draft</option>
                             <option value="final">Final</option>
                         </select>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="closeModal('appraisalModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
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