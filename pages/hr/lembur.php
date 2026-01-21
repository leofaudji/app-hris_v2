<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Manajemen Lembur</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola pengajuan dan persetujuan lembur karyawan.</p>
        </div>
        <div class="flex gap-2">
            <select id="filter-bulan-lembur" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                <option value="">Semua Bulan</option>
                <?php for($m=1; $m<=12; $m++) echo "<option value='$m' ".($m==date('n')?'selected':'').">".date('F', mktime(0,0,0,$m,1))."</option>"; ?>
            </select>
            <select id="filter-tahun-lembur" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                <?php for($y=date('Y'); $y>=date('Y')-2; $y--) echo "<option value='$y'>$y</option>"; ?>
            </select>
            <button onclick="openModal('lemburModal')" class="bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                <i class="bi bi-plus-lg mr-2"></i>Ajukan Lembur
            </button>
        </div>
    </div>

    <!-- Tabel Lembur -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jam</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Durasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="lembur-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr><td colspan="7" class="text-center py-4">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Lembur -->
<div id="lemburModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal('lemburModal')"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="lembur-form">
                <input type="hidden" name="action" value="save">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Form Pengajuan Lembur</h3>
                    
                    <?php 
                    $is_admin = (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') || (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);
                    if($is_admin): 
                    ?>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Karyawan</label>
                        <select name="karyawan_id" id="lembur_karyawan_id" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                            <option value="">Pilih Karyawan</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan / Pekerjaan</label>
                        <textarea name="keterangan" rows="3" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required placeholder="Jelaskan pekerjaan yang dilakukan..."></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                    <button type="button" onclick="closeModal('lemburModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
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