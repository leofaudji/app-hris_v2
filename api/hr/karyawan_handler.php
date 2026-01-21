<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/bootstrap.php';

// Cek autentikasi
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Ambil data karyawan dengan filter pencarian dan status
        $sql = "SELECT k.*, j.nama_jabatan, d.nama_divisi, jk.nama_jadwal, kt.nama_kantor, gg.nama_golongan as nama_golongan_gaji,
                (SELECT id FROM hr_evaluasi_probation WHERE karyawan_id = k.id ORDER BY created_at DESC LIMIT 1) as latest_probation_evaluation_id,
                (SELECT COUNT(*) FROM hr_evaluasi_probation WHERE karyawan_id = k.id) as probation_history_count,
                (SELECT COUNT(*) FROM hr_riwayat_gaji WHERE karyawan_id = k.id) as salary_history_count
                FROM hr_karyawan k 
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id 
                LEFT JOIN hr_jadwal_kerja jk ON k.jadwal_kerja_id = jk.id
                LEFT JOIN hr_divisi d ON k.divisi_id = d.id
                LEFT JOIN hr_kantor kt ON k.kantor_id = kt.id
                LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($_GET['search'])) {
            $search = "%" . $_GET['search'] . "%";
            $sql .= " AND (k.nip LIKE ? OR k.nama_lengkap LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        if (!empty($_GET['status'])) {
            $sql .= " AND k.status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }

        $sql .= " ORDER BY k.nama_lengkap ASC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_probation_history') {
        $karyawan_id = (int)$_POST['karyawan_id'];
        
        $sql = "SELECT ep.*, u.nama_lengkap as nama_penilai 
                FROM hr_evaluasi_probation ep
                LEFT JOIN users u ON ep.penilai_id = u.id
                WHERE ep.karyawan_id = ? 
                ORDER BY ep.tanggal_evaluasi DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $karyawan_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_salary_history') {
        $karyawan_id = (int)$_POST['karyawan_id'];
        
        $sql = "SELECT * FROM hr_riwayat_gaji 
                WHERE karyawan_id = ? 
                ORDER BY tanggal_perubahan DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $karyawan_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            // Validasi Input
            if (empty($_POST['nip']) || empty($_POST['nama_lengkap'])) {
                throw new Exception("NIP dan Nama Lengkap wajib diisi.");
            }

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nip = trim($_POST['nip']);
            $nama_lengkap = trim($_POST['nama_lengkap']);
            $jabatan_id = !empty($_POST['jabatan_id']) ? (int)$_POST['jabatan_id'] : null;
            $jadwal_kerja_id = !empty($_POST['jadwal_kerja_id']) ? (int)$_POST['jadwal_kerja_id'] : null;
            $divisi_id = !empty($_POST['divisi_id']) ? (int)$_POST['divisi_id'] : null;
            $kantor_id = !empty($_POST['kantor_id']) ? (int)$_POST['kantor_id'] : null;
            $golongan_gaji_id = !empty($_POST['golongan_gaji_id']) ? (int)$_POST['golongan_gaji_id'] : null;
            $tanggal_masuk = $_POST['tanggal_masuk'];
            $tanggal_berakhir_kontrak = !empty($_POST['tanggal_berakhir_kontrak']) ? $_POST['tanggal_berakhir_kontrak'] : null;
            $status = $_POST['status'] ?? 'aktif';
            $npwp = $_POST['npwp'] ?? null;
            $status_ptkp = $_POST['status_ptkp'] ?? 'TK/0';
            $ikut_bpjs_kes = isset($_POST['ikut_bpjs_kes']) ? 1 : 0;
            $ikut_bpjs_tk = isset($_POST['ikut_bpjs_tk']) ? 1 : 0;

            // Cek NIP unik (kecuali untuk ID sendiri saat edit)
            $sql_check = "SELECT id FROM hr_karyawan WHERE nip = ?";
            $params_check = [$nip];
            $types_check = "s";
            
            if ($id) {
                $sql_check .= " AND id != ?";
                $params_check[] = $id;
                $types_check .= "i";
            }
            
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param($types_check, ...$params_check);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                throw new Exception("NIP sudah digunakan oleh karyawan lain.");
            }
            $stmt_check->close();

            if ($id) {
                // Cek perubahan gaji/golongan untuk riwayat
                $stmt_old = $conn->prepare("SELECT k.golongan_gaji_id, gg.gaji_pokok FROM hr_karyawan k LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id WHERE k.id = ?");
                $stmt_old->bind_param("i", $id);
                $stmt_old->execute();
                $old_data = $stmt_old->get_result()->fetch_assoc();
                $stmt_old->close();

                // Ambil gaji baru berdasarkan golongan baru
                $gaji_baru = 0;
                if ($golongan_gaji_id) {
                    $stmt_new_gaji = $conn->prepare("SELECT gaji_pokok FROM hr_golongan_gaji WHERE id = ?");
                    $stmt_new_gaji->bind_param("i", $golongan_gaji_id);
                    $stmt_new_gaji->execute();
                    $res_new_gaji = $stmt_new_gaji->get_result()->fetch_assoc();
                    if ($res_new_gaji) $gaji_baru = $res_new_gaji['gaji_pokok'];
                    $stmt_new_gaji->close();
                }

                $gaji_lama = $old_data['gaji_pokok'] ?? 0;

                // Jika ada perubahan golongan dan gaji berbeda, catat riwayat
                if ($old_data['golongan_gaji_id'] != $golongan_gaji_id && $gaji_lama != $gaji_baru) {
                    $tgl_perubahan = date('Y-m-d');
                    $ket_perubahan = "Perubahan Golongan Gaji";
                    $stmt_hist = $conn->prepare("INSERT INTO hr_riwayat_gaji (karyawan_id, tanggal_perubahan, gaji_lama, gaji_baru, keterangan) VALUES (?, ?, ?, ?, ?)");
                    $stmt_hist->bind_param("isdds", $id, $tgl_perubahan, $gaji_lama, $gaji_baru, $ket_perubahan);
                    $stmt_hist->execute();
                }

                // Update Data
                $stmt = $conn->prepare("UPDATE hr_karyawan SET nip=?, nama_lengkap=?, jabatan_id=?, jadwal_kerja_id=?, divisi_id=?, kantor_id=?, golongan_gaji_id=?, tanggal_masuk=?, tanggal_berakhir_kontrak=?, status=?, npwp=?, status_ptkp=?, ikut_bpjs_kes=?, ikut_bpjs_tk=? WHERE id=?");
                $stmt->bind_param("ssiiiiisssssii", $nip, $nama_lengkap, $jabatan_id, $jadwal_kerja_id, $divisi_id, $kantor_id, $golongan_gaji_id, $tanggal_masuk, $tanggal_berakhir_kontrak, $status, $npwp, $status_ptkp, $ikut_bpjs_kes, $ikut_bpjs_tk, $id);
            } else {
                // Insert Data Baru
                $stmt = $conn->prepare("INSERT INTO hr_karyawan (nip, nama_lengkap, jabatan_id, jadwal_kerja_id, divisi_id, kantor_id, golongan_gaji_id, tanggal_masuk, tanggal_berakhir_kontrak, status, npwp, status_ptkp, ikut_bpjs_kes, ikut_bpjs_tk) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiiiiisssssii", $nip, $nama_lengkap, $jabatan_id, $jadwal_kerja_id, $divisi_id, $kantor_id, $golongan_gaji_id, $tanggal_masuk, $tanggal_berakhir_kontrak, $status, $npwp, $status_ptkp, $ikut_bpjs_kes, $ikut_bpjs_tk);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data karyawan berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan data: " . $stmt->error);
            }

        } elseif ($action === 'delete') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (!$id) throw new Exception("ID tidak valid.");

            $stmt = $conn->prepare("DELETE FROM hr_karyawan WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data karyawan berhasil dihapus.']);
            } else {
                throw new Exception("Gagal menghapus data: " . $stmt->error);
            }
        }elseif ($action === 'save_probation_evaluation') {
            $karyawan_id = (int)$_POST['karyawan_id'];
            $tanggal_evaluasi = $_POST['tanggal_evaluasi'];
            $skor_teknis = (int)$_POST['skor_teknis'];
            $skor_budaya = (int)$_POST['skor_budaya'];
            $rekomendasi = $_POST['rekomendasi'];
            $catatan = $_POST['catatan'];
            $penilai_id = $_SESSION['user_id'];

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO hr_evaluasi_probation (karyawan_id, tanggal_evaluasi, penilai_id, skor_teknis, skor_budaya, rekomendasi, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isiisss", $karyawan_id, $tanggal_evaluasi, $penilai_id, $skor_teknis, $skor_budaya, $rekomendasi, $catatan);
                $stmt->execute();

                // Jika rekomendasi adalah angkat tetap atau terminasi, update status karyawan
                if ($rekomendasi === 'angkat_tetap') {
                    $conn->query("UPDATE hr_karyawan SET status = 'aktif' WHERE id = $karyawan_id");
                } elseif ($rekomendasi === 'terminasi') {
                    $conn->query("UPDATE hr_karyawan SET status = 'nonaktif' WHERE id = $karyawan_id");
                    // Optional: Nonaktifkan user login juga
                    $conn->query("UPDATE users SET is_active = 0 WHERE id = (SELECT user_id FROM hr_karyawan WHERE id = $karyawan_id)");
                }
                // Jika perpanjang_probation, status tetap 'probation', mungkin update tanggal berakhir kontrak jika ada fieldnya

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Evaluasi probation berhasil disimpan.']);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        } else {
            throw new Exception("Action tidak valid.");
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}