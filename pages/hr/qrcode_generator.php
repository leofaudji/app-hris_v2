<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="pb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="bi bi-qr-code text-primary"></i> Generator QR Code Absensi
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Konfigurasi -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Konfigurasi Lokasi</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Kantor</label>
                        <select id="qr-kantor-select" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">-- Pilih Kantor --</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label Lokasi (Opsional)</label>
                        <input type="text" id="qr-lokasi-input" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="Contoh: Lobby Utama, Pos Satpam">
                        <p class="text-xs text-gray-500 mt-1">Jika kosong, akan menggunakan nama kantor.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Layout Tampilan</label>
                        <select id="qr-layout-select" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="modern">Modern (Default)</option>
                            <option value="minimalist">Minimalist (Terang)</option>
                            <option value="corporate">Corporate (Gelap)</option>
                        </select>
                    </div>

                    <button onclick="generateQrCode()" class="w-full bg-primary hover:bg-primary-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex justify-center items-center gap-2">
                        <i class="bi bi-arrow-clockwise"></i> Generate QR Code
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview QR Code -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col items-center justify-center min-h-[400px]">
                <div id="qr-preview-container" class="text-center hidden">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2" id="preview-title">Kantor Pusat</h3>
                    <div class="bg-white p-4 rounded-lg shadow-lg inline-block mb-4 border border-gray-200">
                        <img id="qr-image" src="" alt="QR Code" class="w-64 h-64 object-contain">
                    </div>
                    <p class="text-sm text-gray-500 mb-6">Scan QR Code ini menggunakan menu Absensi di Portal Karyawan.<br><span class="text-red-500 text-xs">QR Code berubah setiap 30 detik untuk keamanan.</span></p>
                    
                    <div class="flex gap-3 justify-center">
                        <button onclick="printQrCode()" class="bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                            <i class="bi bi-printer"></i> Cetak
                        </button>
                        <a id="public-link" href="#" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                            <i class="bi bi-box-arrow-up-right"></i> Buka Tampilan Publik
                        </a>
                    </div>
                </div>
                
                <div id="qr-placeholder" class="text-center text-gray-400">
                    <i class="bi bi-qr-code-scan text-6xl mb-3 block"></i>
                    <p>Pilih kantor dan klik Generate untuk melihat QR Code.</p>
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