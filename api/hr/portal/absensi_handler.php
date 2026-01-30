<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Get karyawan_id from user_id
    $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
    $stmt_k->bind_param("i", $user_id);
    $stmt_k->execute();
    $karyawan = $stmt_k->get_result()->fetch_assoc();

    if (!$karyawan) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Data karyawan tidak ditemukan untuk user ini.']);
        exit;
    }
    $karyawan_id = $karyawan['id'];

    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';

        if ($action === 'today_status') {
            // Cek status absensi hari ini
            $today = date('Y-m-d');
            $stmt = $conn->prepare("SELECT * FROM hr_absensi WHERE karyawan_id = ? AND tanggal = ?");
            $stmt->bind_param("is", $karyawan_id, $today);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            // List History
            $bulan = (int)($_GET['bulan'] ?? date('n'));
            $tahun = (int)($_GET['tahun'] ?? date('Y'));

            $sql = "SELECT a.*, s.badge_class 
                    FROM hr_absensi a
                    LEFT JOIN hr_absensi_status s ON a.status = s.nama_status
                    WHERE a.karyawan_id = ? AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
                    ORDER BY a.tanggal DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $karyawan_id, $bulan, $tahun);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode(['success' => true, 'data' => $data]);
        }
    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';
        $today = date('Y-m-d');
        $now = date('H:i:s');
        $lokasi = $_POST['lokasi'] ?? '';
        $foto_base64 = $_POST['foto'] ?? '';
        $qr_content = $_POST['qr_content'] ?? '';
        $jenis_absensi = 'manual'; // Default

        // Validasi QR Code jika ada (untuk mencegah pemalsuan)
        if (!empty($qr_content)) {
            if (strpos($qr_content, 'SECURE:') === 0) {
                $payload = substr($qr_content, 7);
                $decoded = base64_decode($payload, true);
                if ($decoded === false) {
                    throw new Exception("QR Code tidak valid (format base64 salah).");
                }

                $iv_len = openssl_cipher_iv_length('aes-256-cbc');
                $iv = substr($decoded, 0, $iv_len);
                $ciphertext = substr($decoded, $iv_len);
                
                $key = Config::get('APP_KEY') ?? 'rahasia_perusahaan_hris_secure_key';
                $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
                $qrData = json_decode($decrypted, true);

                if (!$qrData) {
                    throw new Exception("QR Code tidak valid atau rusak.");
                }

                // Validasi Timestamp (Mencegah Replay Attack) - Berlaku 60 detik
                if (isset($qrData['generated_at']) && (time() - $qrData['generated_at'] > 60)) {
                    throw new Exception("QR Code sudah kadaluarsa. Silakan scan ulang.");
                }

                // Data QR valid ($qrData['kantor_id'], $qrData['lokasi']) bisa digunakan untuk validasi lebih lanjut
            }
            $jenis_absensi = 'qrcode';
        }

        // Handle Image Upload from Base64
        $foto_path = null;
        if (!empty($foto_base64)) {
            $uploadDir = PROJECT_ROOT . '/uploads/absensi/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if (preg_match('/^data:image\/(\w+);base64,/', $foto_base64, $type)) {
                $image_type = strtolower($type[1]); // jpg, png, gif
                if (!in_array($image_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new Exception('Tipe gambar tidak didukung.');
                }
                $image_base64 = base64_decode(substr($foto_base64, strpos($foto_base64, ',') + 1));
                
                if ($image_base64 === false) {
                    throw new Exception('Gagal mendecode gambar.');
                }
                $fileName = $karyawan_id . '_' . time() . '_' . $action . '.' . $image_type;
                $fileFullPath = $uploadDir . $fileName;
                if (file_put_contents($fileFullPath, $image_base64)) {
                    $foto_path = 'uploads/absensi/' . $fileName;
                }
            } else {
                throw new Exception('Format data gambar tidak valid.');
            }
            if ($jenis_absensi === 'manual') {
                $jenis_absensi = 'selfie';
            }
        }

        if ($action === 'clock_in') {
            // Cek apakah sudah absen masuk
            $check = $conn->prepare("SELECT id FROM hr_absensi WHERE karyawan_id = ? AND tanggal = ?");
            $check->bind_param("is", $karyawan_id, $today);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Anda sudah melakukan absen masuk hari ini.");
            }

            // Insert Absen Masuk
            $stmt = $conn->prepare("INSERT INTO hr_absensi (karyawan_id, tanggal, jam_masuk, status, lokasi_masuk, foto_masuk, jenis_absensi) VALUES (?, ?, ?, 'hadir', ?, ?, ?)");
            $stmt->bind_param("isssss", $karyawan_id, $today, $now, $lokasi, $foto_path, $jenis_absensi);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Berhasil Absen Masuk pada ' . $now]);
            } else {
                throw new Exception("Gagal absen masuk: " . $stmt->error);
            }

        } elseif ($action === 'clock_out') {
            // Cek apakah sudah absen masuk
            $check = $conn->prepare("SELECT id, jam_keluar FROM hr_absensi WHERE karyawan_id = ? AND tanggal = ?");
            $check->bind_param("is", $karyawan_id, $today);
            $check->execute();
            $row = $check->get_result()->fetch_assoc();

            if (!$row) {
                throw new Exception("Anda belum melakukan absen masuk hari ini.");
            }
            if (!empty($row['jam_keluar'])) {
                throw new Exception("Anda sudah melakukan absen pulang hari ini.");
            }

            // Update Absen Keluar
            $stmt = $conn->prepare("UPDATE hr_absensi SET jam_keluar = ?, lokasi_keluar = ?, foto_keluar = ? WHERE id = ?");
            $stmt->bind_param("sssi", $now, $lokasi, $foto_path, $row['id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Berhasil Absen Pulang pada ' . $now]);
            } else {
                throw new Exception("Gagal absen pulang: " . $stmt->error);
            }
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}