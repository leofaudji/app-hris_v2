<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Pengajuan Lembur</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Pengajuan -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Form Lembur</h3>
                <form id="portal-lembur-form" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan / Tugas</label>
                        <textarea name="keterangan" rows="3" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm sm:text-sm" placeholder="Jelaskan pekerjaan yang dilakukan..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-primary hover:bg-primary-600 text-white px-4 py-2.5 rounded-lg font-semibold transition-colors flex justify-center items-center gap-2">
                        <i class="bi bi-send-fill"></i> Ajukan Lembur
                    </button>
                </form>
            </div>
        </div>

        <!-- Riwayat Lembur -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Pengajuan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Jam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Keterangan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="portal-lembur-body" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr><td colspan="5" class="text-center py-6 text-gray-500">Memuat data...</td></tr>
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