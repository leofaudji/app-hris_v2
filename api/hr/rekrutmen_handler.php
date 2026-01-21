<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/bootstrap.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list_vacancies';

        if ($action === 'list_vacancies') {
            $sql = "SELECT l.*, j.nama_jabatan, d.nama_divisi, 
                    (SELECT COUNT(*) FROM hr_pelamar WHERE lowongan_id = l.id) as total_pelamar
                    FROM hr_lowongan l
                    LEFT JOIN hr_jabatan j ON l.jabatan_id = j.id
                    LEFT JOIN hr_divisi d ON l.divisi_id = d.id
                    ORDER BY l.created_at DESC";
            $data = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }
        elseif ($action === 'list_applicants') {
            $lowongan_id = $_GET['lowongan_id'] ?? null;
            $where = $lowongan_id ? "WHERE p.lowongan_id = " . (int)$lowongan_id : "";
            
            $sql = "SELECT p.*, l.judul as judul_lowongan 
                    FROM hr_pelamar p
                    JOIN hr_lowongan l ON p.lowongan_id = l.id
                    $where
                    ORDER BY p.tanggal_lamar DESC";
            $data = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }

    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_vacancy') {
            $id = $_POST['id'] ?? null;
            $judul = $_POST['judul'];
            $jabatan_id = $_POST['jabatan_id'];
            $divisi_id = $_POST['divisi_id'];
            $kuota = $_POST['kuota'];
            $deskripsi = $_POST['deskripsi'];
            $kualifikasi = $_POST['kualifikasi'];
            $status = $_POST['status'];
            $tanggal_mulai = $_POST['tanggal_mulai'];
            $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;

            if ($id) {
                $stmt = $conn->prepare("UPDATE hr_lowongan SET judul=?, jabatan_id=?, divisi_id=?, kuota=?, deskripsi=?, kualifikasi=?, status=?, tanggal_mulai=?, tanggal_selesai=? WHERE id=?");
                $stmt->bind_param("siiisssssi", $judul, $jabatan_id, $divisi_id, $kuota, $deskripsi, $kualifikasi, $status, $tanggal_mulai, $tanggal_selesai, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO hr_lowongan (judul, jabatan_id, divisi_id, kuota, deskripsi, kualifikasi, status, tanggal_mulai, tanggal_selesai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("siiisssss", $judul, $jabatan_id, $divisi_id, $kuota, $deskripsi, $kualifikasi, $status, $tanggal_mulai, $tanggal_selesai);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Lowongan berhasil disimpan.']);
            } else {
                throw new Exception("Gagal menyimpan: " . $stmt->error);
            }
        }
        elseif ($action === 'save_applicant') {
            // Manual entry by HR
            $lowongan_id = $_POST['lowongan_id'];
            $nama = $_POST['nama_lengkap'];
            $email = $_POST['email'];
            $hp = $_POST['no_hp'];
            $pendidikan = $_POST['pendidikan_terakhir'];
            $catatan = $_POST['catatan'];
            
            $file_cv = null;
            if (isset($_FILES['file_cv']) && $_FILES['file_cv']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PROJECT_ROOT . '/uploads/cv/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileName = time() . '_' . basename($_FILES['file_cv']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['file_cv']['tmp_name'], $targetPath)) {
                    $file_cv = 'uploads/cv/' . $fileName;
                }
            }

            $stmt = $conn->prepare("INSERT INTO hr_pelamar (lowongan_id, nama_lengkap, email, no_hp, pendidikan_terakhir, catatan, file_cv, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'applied')");
            $stmt->bind_param("issssss", $lowongan_id, $nama, $email, $hp, $pendidikan, $catatan, $file_cv);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Pelamar berhasil ditambahkan.']);
            } else {
                throw new Exception("Gagal menyimpan: " . $stmt->error);
            }
        }
        elseif ($action === 'update_applicant_status') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $stmt = $conn->prepare("UPDATE hr_pelamar SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status pelamar diperbarui.']);
            } else {
                throw new Exception("Gagal update status.");
            }
        }
        elseif ($action === 'convert_to_employee') {
            // Proses Hired -> Karyawan
            $pelamar_id = $_POST['pelamar_id'];
            $nip = $_POST['nip'];
            $tanggal_masuk = $_POST['tanggal_masuk'];
            $kantor_id = $_POST['kantor_id'];
            $jadwal_kerja_id = $_POST['jadwal_kerja_id'];
            $golongan_gaji_id = $_POST['golongan_gaji_id'];
            $status_karyawan = $_POST['status_karyawan'];

            // Ambil tanggal berakhir dari input (untuk probation/kontrak)
            $tanggal_berakhir_kontrak = !empty($_POST['tanggal_berakhir_kontrak']) ? $_POST['tanggal_berakhir_kontrak'] : null;

            $conn->begin_transaction();
            try {
                // 1. Ambil data pelamar & lowongan
                $stmt_p = $conn->prepare("SELECT p.*, l.jabatan_id, l.divisi_id FROM hr_pelamar p JOIN hr_lowongan l ON p.lowongan_id = l.id WHERE p.id = ?");
                $stmt_p->bind_param("i", $pelamar_id);
                $stmt_p->execute();
                $pelamar = $stmt_p->get_result()->fetch_assoc();

                if (!$pelamar) throw new Exception("Data pelamar tidak ditemukan.");

                // 2. Cek NIP
                $stmt_check = $conn->prepare("SELECT id FROM hr_karyawan WHERE nip = ?");
                $stmt_check->bind_param("s", $nip);
                $stmt_check->execute();
                if ($stmt_check->get_result()->num_rows > 0) throw new Exception("NIP sudah digunakan.");

                // 3. Insert Karyawan
                $stmt_ins = $conn->prepare("INSERT INTO hr_karyawan (nip, nama_lengkap, jabatan_id, divisi_id, kantor_id, jadwal_kerja_id, golongan_gaji_id, tanggal_masuk, tanggal_berakhir_kontrak, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_ins->bind_param("ssiiiiisss", 
                    $nip, 
                    $pelamar['nama_lengkap'], 
                    $pelamar['jabatan_id'], 
                    $pelamar['divisi_id'], 
                    $kantor_id, 
                    $jadwal_kerja_id, 
                    $golongan_gaji_id, 
                    $tanggal_masuk, 
                    $tanggal_berakhir_kontrak,
                    $status_karyawan
                );
                $stmt_ins->execute();
                $karyawan_id = $conn->insert_id;

                // 4. Buat User Login (Default password: nip)
                $username = strtolower(str_replace(' ', '', $pelamar['nama_lengkap'])) . rand(10,99);
                $password = password_hash($nip, PASSWORD_DEFAULT); // Password awal = NIP
                $role_id = 4; // Role Karyawan
                
                $stmt_user = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, role_id) VALUES (?, ?, ?, ?)");
                $stmt_user->bind_param("sssi", $username, $password, $pelamar['nama_lengkap'], $role_id);
                $stmt_user->execute();
                $user_id = $conn->insert_id;

                // 5. Link User ke Karyawan
                $conn->query("UPDATE hr_karyawan SET user_id = $user_id WHERE id = $karyawan_id");

                // 6. Update Status Pelamar jadi Hired
                $conn->query("UPDATE hr_pelamar SET status = 'hired' WHERE id = $pelamar_id");

                // 7. Generate Jatah Cuti Awal
                $tahun = date('Y');
                $conn->query("INSERT INTO hr_jatah_cuti (karyawan_id, tahun, jatah_awal, sisa_jatah) VALUES ($karyawan_id, $tahun, 12, 12)");

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Pelamar berhasil dikonversi menjadi karyawan. Username: ' . $username]);

            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }
        elseif ($action === 'delete_vacancy') {
            $id = $_POST['id'];
            $conn->query("DELETE FROM hr_lowongan WHERE id = $id"); // Cascade delete pelamar handled by DB logic usually, but here simple delete
            $conn->query("DELETE FROM hr_pelamar WHERE lowongan_id = $id");
            echo json_encode(['success' => true, 'message' => 'Lowongan dihapus.']);
        }
        elseif ($action === 'upload_signed_spk') {
            $id = $_POST['id'];
            
            if (isset($_FILES['file_spk']) && $_FILES['file_spk']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PROJECT_ROOT . '/uploads/spk/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileName = time() . '_SPK_SIGNED_' . $id . '_' . basename($_FILES['file_spk']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['file_spk']['tmp_name'], $targetPath)) {
                    $filePath = 'uploads/spk/' . $fileName;
                    $stmt = $conn->prepare("UPDATE hr_pelamar SET file_spk_signed = ? WHERE id = ?");
                    $stmt->bind_param("si", $filePath, $id);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Dokumen SPK berhasil diunggah.']);
                    } else {
                        throw new Exception("Gagal update database.");
                    }
                } else {
                    throw new Exception("Gagal memindahkan file.");
                }
            } else {
                throw new Exception("Tidak ada file yang diunggah atau terjadi error.");
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>