<?php
// Mulai atau lanjutkan sesi.
// Ini harus menjadi baris pertama sebelum output apa pun.
if (session_status() === PHP_SESSION_NONE) {
    // Hardening Session Cookie
    // HttpOnly: Cookie tidak bisa diakses via JavaScript (mencegah pencurian via XSS)
    // SameSite=Lax: Cookie tidak dikirim jika request berasal dari website lain (mencegah CSRF)
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (hilang saat browser tutup)
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']), // Hanya kirim via HTTPS jika tersedia
            'httponly' => true,
            'samesite' => 'Lax' 
        ]);
    } else {
        // Fallback untuk PHP versi lama
        session_set_cookie_params(0, '/', '', isset($_SERVER['HTTPS']), true);
    }
    session_start();
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}



require_once __DIR__ . '/functions.php';
// Muat autoloader Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/RateLimiter.php';

/**
 * Mengembalikan CSRF token saat ini.
 * @return string
 */
function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Memverifikasi CSRF token untuk permintaan yang tidak aman (POST, PUT, DELETE).
 * Akan menghentikan eksekusi jika token tidak valid.
 */
function verify_csrf_token() {
    // Hanya periksa untuk metode yang mengubah state
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Ambil token dari POST body atau dari header (untuk API)
        $token_from_request = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

        if (empty($token_from_request)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Permintaan tidak valid. Token CSRF tidak ada.']));
        }

        // Gunakan hash_equals untuk perbandingan yang aman dari timing attack
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_from_request)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Token CSRF tidak cocok. Silakan muat ulang halaman.']));
        }
    }
}

/**
 * Mencoba untuk login pengguna menggunakan data dari cookie.
 * @param string $selector
 * @param string $validator
 */
function attempt_login_with_cookie($selector, $validator) {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("SELECT id, username, role, role_id, nama_lengkap, remember_validator_hash FROM users WHERE remember_selector = ?");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $validator_hash_from_db = $user['remember_validator_hash'];
        if (hash_equals($validator_hash_from_db, hash('sha256', $validator))) {
            // Token valid, login pengguna
            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['role_id'] = $user['role_id'];

            // --- Token Rotation (Penting untuk Keamanan) ---
            // Buat token baru untuk mencegah pencurian cookie
            $new_selector = bin2hex(random_bytes(16));
            $new_validator = bin2hex(random_bytes(32));
            $new_validator_hash = hash('sha256', $new_validator);
            $expires = time() + (86400 * 30); // 30 hari

            $stmt_rotate = $conn->prepare("UPDATE users SET remember_selector = ?, remember_validator_hash = ? WHERE id = ?");
            $stmt_rotate->bind_param("ssi", $new_selector, $new_validator_hash, $user['id']);
            $stmt_rotate->execute();
            $stmt_rotate->close();

            setcookie(
                'remember_me',
                $new_selector . ':' . $new_validator,
                $expires,
                BASE_PATH . '/',
                "",
                isset($_SERVER['HTTPS']),
                true
            );
        } else {
            // Token tidak cocok, hapus dari DB dan cookie untuk keamanan
            // (menandakan kemungkinan pencurian cookie)
            $stmt_clear = $conn->prepare("UPDATE users SET remember_selector = NULL, remember_validator_hash = NULL WHERE remember_selector = ?");
            $stmt_clear->bind_param("s", $selector);
            $stmt_clear->execute();
            setcookie('remember_me', '', time() - 3600, BASE_PATH . '/');
        }
    }
}

/**
 * Mengambil nominal iuran yang berlaku untuk periode tertentu dari histori.
 *
 * @param int $tahun
 * @param int $bulan
 * @return float
 */
function get_fee_for_period($tahun, $bulan) {
    $conn = Database::getInstance()->getConnection();
    // Menggunakan hari pertama dari bulan yang diminta sebagai acuan
    $date_for_period = "$tahun-$bulan-01";
    
    $stmt = $conn->prepare(
        "SELECT monthly_fee FROM iuran_settings_history 
         WHERE start_date <= ? AND (end_date IS NULL OR end_date >= ?)
         ORDER BY start_date DESC LIMIT 1"
    );
    $stmt->bind_param("ss", $date_for_period, $date_for_period);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Jika ada histori, gunakan itu. Jika tidak, fallback ke pengaturan umum.
    return $result ? (float)$result['monthly_fee'] : (float)get_setting('monthly_fee', 50000);
}

// Define project root path for reliable file includes.
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

// Define base path dynamically so it's available globally.
if (!defined('BASE_PATH')) {
    // This is a more robust way to determine the base path,
    // as it doesn't rely on SCRIPT_NAME which can be inconsistent across server configurations.
    // It calculates the path relative to the document root.
    $projectRoot = str_replace('\\', '/', PROJECT_ROOT);
    // Ensure DOCUMENT_ROOT has no trailing slash for consistency.
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');

    $basePath = str_replace($docRoot, '', $projectRoot);

    define('BASE_PATH', rtrim($basePath, '/')); // Should correctly resolve to "/app-rt"
}

// Load environment variables from the root directory
try {
    Config::load(PROJECT_ROOT . '/.env');
} catch (\Exception $e) {
    die('Error: Could not load configuration. Make sure a .env file exists in the root directory. Details: ' . $e->getMessage());
}

// --- Terapkan Rate Limiting untuk API ---
// Cek apakah permintaan saat ini adalah permintaan API
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {    
    // Verifikasi CSRF token untuk semua permintaan API yang mengubah data
    verify_csrf_token();

    // Batasi 60 permintaan per menit per IP
    $rateLimiter = new RateLimiter(60, 60); 
    $clientIp = $_SERVER['REMOTE_ADDR'];
    $rateLimiter->check($clientIp);
}

// --- Maintenance Mode Check ---
if (defined('BASE_PATH')) {
    // Gunakan fungsi get_setting jika tersedia (dari functions.php)
    // Default '0' (mati) jika setting tidak ditemukan
    $maintenance_mode = function_exists('get_setting') ? get_setting('maintenance_mode', '0') : '0';

    if ($maintenance_mode === '1') {
        $request_uri = $_SERVER['REQUEST_URI'];
        $path_only = parse_url($request_uri, PHP_URL_PATH);
        
        // Normalisasi path relatif terhadap BASE_PATH
        $relative_path = $path_only;
        if (BASE_PATH !== '' && strpos($path_only, BASE_PATH) === 0) {
            $relative_path = substr($path_only, strlen(BASE_PATH));
        }
        if ($relative_path === '') $relative_path = '/';

        // Daftar path yang diizinkan saat maintenance (Login & Assets tetap harus bisa diakses)
        $allowed_prefixes = [
            '/login', 
            '/logout',
            '/maintenance',
            '/assets/',
            '/api/auth', // Asumsi endpoint login
        ];
        
        $is_allowed = false;
        foreach ($allowed_prefixes as $prefix) {
            if (strpos($relative_path, $prefix) === 0) {
                $is_allowed = true;
                break;
            }
        }
        
        // Cek User Role (Admin bypass)
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        
        if (!$is_allowed && !$is_admin) {
            header('Location: ' . BASE_PATH . '/maintenance');
            exit;
        }
    }
}