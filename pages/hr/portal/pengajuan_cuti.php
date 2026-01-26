<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Pengajuan Cuti & Izin</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Pengajuan -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Formulir Pengajuan</h3>
                </div>
                <form id="portal-cuti-form" class="p-6 space-y-4" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Cuti/Izin</label>
                        <select name="jenis_cuti_id" id="portal-jenis-cuti" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="portal-tanggal-mulai" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="portal-tanggal-selesai" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lampiran (Opsional)</label>
                        <input type="file" name="lampiran_file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: PDF, JPG, PNG. Maks 2MB.</p>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-primary hover:bg-primary-600 text-white px-4 py-2.5 rounded-lg font-semibold transition-colors">
                            <i class="bi bi-send-fill mr-2"></i>Ajukan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Riwayat Pengajuan -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Pengajuan Saya</h3>
                    <div class="text-sm text-gray-500">Sisa Cuti Tahunan: <strong id="sisa-cuti-display" class="text-primary">-</strong> hari</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Durasi</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="portal-riwayat-cuti-body" class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr><td colspan="5" class="text-center py-6 text-gray-500">Memuat riwayat...</td></tr>
                        </tbody>
                    </table>
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