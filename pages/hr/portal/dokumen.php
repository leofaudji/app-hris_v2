<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dokumen & Surat</h1>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="dokumen-tabs" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 active-tab-border text-primary border-primary" id="tab-perusahaan" data-tabs-target="#content-perusahaan" type="button" role="tab" aria-controls="content-perusahaan" aria-selected="true">Dokumen Perusahaan</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 text-gray-500 dark:text-gray-400" id="tab-request" data-tabs-target="#content-request" type="button" role="tab" aria-controls="content-request" aria-selected="false">Request Surat</button>
            </li>
        </ul>
    </div>

    <div id="dokumen-tab-content">
        <!-- Tab Content: Dokumen Perusahaan -->
        <div class="" id="content-perusahaan" role="tabpanel" aria-labelledby="tab-perusahaan">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dokumen & Kebijakan</h3>
                <div id="list-dokumen-perusahaan" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Dokumen akan dimuat di sini -->
                    <div class="col-span-full text-center py-8 text-gray-500">Memuat dokumen...</div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Request Surat -->
        <div class="hidden" id="content-request" role="tabpanel" aria-labelledby="tab-request">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Request -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Buat Permintaan Baru</h3>
                        </div>
                        <form id="form-request-surat" class="p-6 space-y-4">
                            <input type="hidden" name="action" value="request_letter">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Surat</label>
                                <select name="jenis_surat" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required>
                                    <option value="">Pilih Jenis Surat</option>
                                    <option value="paklaring">Surat Keterangan Kerja (Paklaring)</option>
                                    <option value="keterangan_kerja">Surat Keterangan Masih Aktif Bekerja</option>
                                    <option value="keterangan_visa">Surat Keterangan Visa</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan / Keperluan</label>
                                <textarea name="keterangan" rows="3" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" placeholder="Contoh: Untuk pengajuan KPR, Visa ke Jepang, dll." required></textarea>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="w-full bg-primary hover:bg-primary-600 text-white px-4 py-2.5 rounded-lg font-semibold transition-colors">
                                    <i class="bi bi-send-fill mr-2"></i>Kirim Permintaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Riwayat Request -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Permintaan</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Jenis Surat</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="list-riwayat-request" class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr><td colspan="4" class="text-center py-6 text-gray-500">Memuat riwayat...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>
