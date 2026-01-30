<?php
ob_start(); // Mulai buffering output untuk mencegah whitespace/error merusak gambar
require_once 'includes/bootstrap.php';
require_once PROJECT_ROOT . '/includes/phpqrcode/qrlib.php';

$conn = Database::getInstance()->getConnection();

// Default values. Change these to reflect your actual office/location.
$kantor_id = $_GET['kantor_id'] ?? 1; // Example: Get office ID from parameter
$lokasi = $_GET['lokasi'] ?? 'Kantor Pusat'; // Example: Get location name
$mode = $_GET['mode'] ?? 'view'; // 'view' or 'raw'
$layout = $_GET['layout'] ?? 'modern'; // modern, minimalist, corporate

// --- Mode: Check Status (Polling untuk Auto Reload) ---
if ($mode === 'check_status') {
    ob_clean(); // Bersihkan buffer output
    header('Content-Type: application/json');
    
    // Ambil waktu update terakhir dari data kantor
    $stmt = $conn->prepare("SELECT updated_at FROM hr_kantor WHERE id = ?");
    $stmt->bind_param("i", $kantor_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    
    echo json_encode(['updated_at' => $res['updated_at'] ?? '']);
    exit;
}

// Combine into a string for the QR code.  Make sure this string is NOT predictable or exploitable.
// Encrypt the content to prevent forgery.
$key = Config::get('APP_KEY') ?? 'rahasia_perusahaan_hris_secure_key'; // Fallback key jika .env belum diatur
$data = json_encode([
    'kantor_id' => $kantor_id,
    'lokasi' => $lokasi,
    'generated_at' => time() // Timestamp untuk validasi tambahan jika diperlukan
]);
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
$encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
$qrContent = "SECURE:" . base64_encode($iv . $encrypted);

// Set image size and error correction level.
$ecc = 'H'; // High error correction
$size = 10; // QR code size
$margin = 2; // Adjust margin size as needed.

if ($mode === 'raw') {
    // Output image directly
    error_reporting(0); // Matikan error reporting agar warning tidak merusak stream gambar PNG
    ob_clean(); // Clear any previous output
    header('Content-Type: image/png');
    QRcode::png($qrContent, false, $ecc, $size, $margin);
    exit;
}

// --- Mode: View (Tampilan Halaman) ---
// Ambil data awal untuk referensi polling
$stmt = $conn->prepare("SELECT nama_kantor, updated_at FROM hr_kantor WHERE id = ?");
$stmt->bind_param("i", $kantor_id);
$stmt->execute();
$office = $stmt->get_result()->fetch_assoc();
$current_updated_at = $office['updated_at'] ?? '';

// Ambil Logo Perusahaan
$logo_path = get_setting('app_logo');
$logo_url = $logo_path ? base_url($logo_path) : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi QR Code - <?= htmlspecialchars($office['nama_kantor'] ?? 'Kantor') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        <?php if ($layout === 'modern'): ?>
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .blob {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            opacity: 0.6;
            animation: move 10s infinite alternate;
        }
        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(20px, -20px) scale(1.1); }
        }
        <?php endif; ?>
    </style>
</head>
<body class="<?= $layout === 'minimalist' ? 'bg-gray-100' : ($layout === 'corporate' ? 'bg-neutral-900' : 'bg-slate-900') ?> min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <?php if ($layout === 'modern'): ?>
    <!-- Background Elements -->
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 z-0"></div>
    <div class="blob bg-blue-600 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="blob bg-indigo-600 w-96 h-96 rounded-full bottom-0 right-0 translate-x-1/2 translate-y-1/2"></div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="relative z-10 w-full max-w-md p-6">
        
        <?php if ($layout === 'minimalist'): ?>
        <!-- LAYOUT 2: MINIMALIST (Light & Clean) -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="p-8 text-center">
                <?php if ($logo_url): ?>
                    <img src="<?= $logo_url ?>" alt="Logo" class="h-12 mx-auto mb-4 object-contain">
                <?php else: ?>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-600 mb-4">
                        <i class="bi bi-qr-code-scan text-2xl"></i>
                    </div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold text-gray-900">Absensi Digital</h1>
                <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($office['nama_kantor'] ?? 'Nama Kantor') ?></p>
            </div>

            <div class="px-8 pb-8 flex flex-col items-center">
                <div class="bg-white p-2 rounded-xl border-2 border-dashed border-gray-200 mb-6">
                    <img id="qr-image" src="<?= base_url("/qr/img/" . htmlspecialchars($kantor_id) . "/" . urlencode($lokasi)) ?>" alt="QR Code" class="w-64 h-64 object-contain">
                </div>
                
                <div class="w-full bg-gray-50 rounded-xl p-4 text-center">
                    <div id="live-date" class="text-gray-500 text-sm font-medium mb-1"></div>
                    <div id="live-clock" class="text-3xl font-bold text-gray-800 tracking-tight">--:--:--</div>
                    <div class="mt-2 flex items-center justify-center gap-2 text-xs text-gray-400">
                        <i class="bi bi-geo-alt-fill text-red-500"></i> <?= htmlspecialchars($lokasi) ?>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-3 text-center border-t border-gray-100">
                <p class="text-xs text-gray-400">QR Code refresh otomatis setiap 30 detik</p>
            </div>
        </div>

        <?php elseif ($layout === 'corporate'): ?>
        <!-- LAYOUT 3: CORPORATE (Dark & Solid) -->
        <div class="bg-neutral-800 rounded-xl shadow-2xl overflow-hidden border border-neutral-700">
            <div class="bg-neutral-900 p-6 border-b border-neutral-700 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-white">Absensi Karyawan</h1>
                    <p class="text-neutral-400 text-xs"><?= htmlspecialchars($office['nama_kantor'] ?? 'Nama Kantor') ?></p>
                </div>
                <?php if ($logo_url): ?>
                    <img src="<?= $logo_url ?>" alt="Logo" class="h-8 object-contain">
                <?php else: ?>
                    <i class="bi bi-building text-neutral-500 text-xl"></i>
                <?php endif; ?>
            </div>
            
            <div class="p-8 flex flex-col items-center">
                <div class="relative">
                    <div class="absolute inset-0 bg-yellow-500 blur-lg opacity-20 rounded-full"></div>
                    <div class="relative bg-white p-3 rounded-lg shadow-lg">
                        <img id="qr-image" src="<?= base_url("/qr/img/" . htmlspecialchars($kantor_id) . "/" . urlencode($lokasi)) ?>" alt="QR Code" class="w-64 h-64 object-contain">
                    </div>
                </div>

                <div class="mt-8 w-full grid grid-cols-2 gap-4">
                    <div class="bg-neutral-700/50 p-3 rounded-lg text-center border border-neutral-700">
                        <span class="text-xs text-neutral-400 block uppercase tracking-wider">Lokasi</span>
                        <span class="text-sm font-medium text-white truncate block"><?= htmlspecialchars($lokasi) ?></span>
                    </div>
                    <div class="bg-neutral-700/50 p-3 rounded-lg text-center border border-neutral-700">
                        <span class="text-xs text-neutral-400 block uppercase tracking-wider">Waktu</span>
                        <span id="live-clock" class="text-sm font-medium text-yellow-500 block">--:--:--</span>
                    </div>
                </div>
                <div id="live-date" class="mt-4 text-neutral-500 text-xs text-center"></div>
            </div>
        </div>

        <?php else: ?>
        <!-- LAYOUT 1: MODERN (Default Glassmorphism) -->
        <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center text-white">
                <?php if ($logo_url): ?>
                    <div class="mb-4 flex justify-center">
                        <img src="<?= $logo_url ?>" alt="Logo Perusahaan" class="h-16 object-contain bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20 shadow-sm">
                    </div>
                <?php else: ?>
                    <div class="mb-3 inline-flex items-center justify-center w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm shadow-inner border border-white/30">
                        <i class="bi bi-qr-code-scan text-2xl"></i>
                    </div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold tracking-tight">Absensi Digital</h1>
                <p class="text-blue-100 text-sm mt-1">Scan QR Code untuk melakukan absensi</p>
            </div>

            <!-- Content -->
            <div class="p-8 flex flex-col items-center">
                <!-- Location Info -->
                <div class="w-full mb-6 text-center">
                    <h2 class="text-xl font-bold text-slate-800 mb-1"><?= htmlspecialchars($office['nama_kantor'] ?? 'Nama Kantor') ?></h2>
                    <div class="flex items-center justify-center text-slate-500 text-sm gap-2">
                        <i class="bi bi-geo-alt-fill text-red-500"></i>
                        <span class="font-medium"><?= htmlspecialchars($lokasi) ?></span>
                    </div>
                </div>

                <!-- QR Code Frame -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                        <img id="qr-image" 
                             src="<?= base_url("/qr/img/" . htmlspecialchars($kantor_id) . "/" . urlencode($lokasi)) ?>" 
                             alt="QR Code Absensi" 
                             class="w-64 h-64 object-contain">
                    </div>
                </div>

                <!-- Instructions / Status -->
                <div class="mt-8 text-center space-y-4 w-full">
                    <div id="live-date" class="text-slate-600 font-medium text-lg"></div>
                    <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-blue-50 text-blue-700 text-lg font-bold shadow-sm border border-blue-100">
                        <i class="bi bi-clock"></i>
                        <span id="live-clock">--:--:--</span>
                    </div>
                    
                    <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
                        <i class="bi bi-shield-check text-green-500"></i>
                        QR Code diperbarui otomatis setiap 30 detik
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
                <p class="text-xs text-slate-400 font-medium">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars(get_setting('app_name', 'App HRIS')) ?>. All rights reserved.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

<script>
    // Clock Function
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('live-date').textContent = now.toLocaleDateString('id-ID', options);
        document.getElementById('live-clock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 1. Refresh Gambar QR Code (Anti-Replay Attack)
    setInterval(function() {
        var img = document.getElementById('qr-image');
        var src = img.src;
        // Tambahkan timestamp untuk mencegah caching browser dan memaksa generate baru
        if (src.indexOf('&t=') > -1) src = src.split('&t=')[0];
        img.src = src + '&t=' + new Date().getTime();
    }, 30000); // Refresh setiap 30 detik

    // 2. Auto Reload Halaman jika Konfigurasi Kantor Berubah
    const currentUpdatedAt = "<?= $current_updated_at ?>";
    const checkUrl = "<?= base_url('public_qrcode.php') ?>?mode=check_status&kantor_id=<?= $kantor_id ?>";

    setInterval(() => {
        fetch(checkUrl)
            .then(response => response.json())
            .then(data => {
                // Jika timestamp updated_at di database berbeda dengan saat halaman dimuat, reload halaman
                if (data.updated_at !== currentUpdatedAt) {
                    console.log('Konfigurasi berubah, memuat ulang...');
                    location.reload();
                }
            })
            .catch(err => console.error('Gagal mengecek status:', err));
    }, 15000); // Cek setiap 15 detik
</script>
</body>
</html>