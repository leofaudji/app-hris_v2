<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex justify-between flex-wrap items-center pt-3 pb-2 mb-3 border-b border-gray-200 dark:border-gray-700">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <i class="bi bi-calculator-fill"></i> Pengaturan Pajak & BPJS
    </h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <form id="pajak-settings-form">
        <input type="hidden" name="action" value="save_settings">
        
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Penghasilan Tidak Kena Pajak (PTKP) Tahunan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <?php
            $ptkp_list = [
                'ptkp_tk0' => 'TK/0 (Tidak Kawin, 0 Tanggungan)',
                'ptkp_k0' => 'K/0 (Kawin, 0 Tanggungan)',
                'ptkp_k1' => 'K/1 (Kawin, 1 Tanggungan)',
                'ptkp_k2' => 'K/2 (Kawin, 2 Tanggungan)',
                'ptkp_k3' => 'K/3 (Kawin, 3 Tanggungan)',
            ];
            foreach ($ptkp_list as $key => $label) {
                echo '<div>
                    <label for="'.$key.'" class="block text-sm font-medium text-gray-700 dark:text-gray-300">'.$label.'</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">Rp</span></div>
                        <input type="number" name="'.$key.'" id="'.$key.'" class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                    </div>
                </div>';
            }
            ?>
        </div>

        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-t border-gray-200 dark:border-gray-700 pt-4">Tarif BPJS (%)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">BPJS Kesehatan (Ditanggung Perusahaan)</label>
                <input type="number" step="0.01" name="bpjs_kes_perusahaan" id="bpjs_kes_perusahaan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">BPJS Kesehatan (Ditanggung Karyawan)</label>
                <input type="number" step="0.01" name="bpjs_kes_karyawan" id="bpjs_kes_karyawan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">JHT (Ditanggung Perusahaan)</label>
                <input type="number" step="0.01" name="bpjs_tk_jht_perusahaan" id="bpjs_tk_jht_perusahaan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">JHT (Ditanggung Karyawan)</label>
                <input type="number" step="0.01" name="bpjs_tk_jht_karyawan" id="bpjs_tk_jht_karyawan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
             <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">JP (Ditanggung Perusahaan)</label>
                <input type="number" step="0.01" name="bpjs_tk_jp_perusahaan" id="bpjs_tk_jp_perusahaan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">JP (Ditanggung Karyawan)</label>
                <input type="number" step="0.01" name="bpjs_tk_jp_karyawan" id="bpjs_tk_jp_karyawan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" id="save-pajak-btn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="bi bi-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>