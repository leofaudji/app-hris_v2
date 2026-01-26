<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Cek login dan role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

verify_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = isset($_POST['status']) && $_POST['status'] === 'true' ? '1' : '0';
    
    $conn = Database::getInstance()->getConnection();
    
    // Cek apakah setting sudah ada
    $check = $conn->query("SELECT setting_key FROM settings WHERE setting_key = 'maintenance_mode'");
    if ($check->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_mode', ?)");
        $stmt->bind_param("s", $status);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Mode Maintenance berhasil ' . ($status === '1' ? 'diaktifkan' : 'dinonaktifkan') . '.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengaturan.']);
    }
}