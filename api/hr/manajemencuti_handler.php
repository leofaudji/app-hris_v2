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
        $action = $_GET['action'] ?? 'list';
        if ($action === 'get_sisa_cuti') {
            $karyawan_id = isset($_GET['karyawan_id']) ? (int)$_GET['karyawan_id'] : 0;
            // Jika bukan admin dan tidak ada karyawan_id di GET, ambil dari user login
            if ((!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) && empty($karyawan_id)) {
                $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
                if ($stmt_k) {
                    $stmt_k->bind_param("i", $_SESSION['user_id']);
                    $stmt_k->execute();
                    $res_k = $stmt_k->get_result();
                    if ($row_k = $res_k->fetch_assoc()) {
                        $karyawan_id = $row_k['id'];
                    }
                }
            }

            $tahun = (int)$_GET['tahun'];
            $stmt = $conn->prepare("SELECT sisa_jatah FROM hr_jatah_cuti WHERE karyawan_id = ? AND tahun = ?");
            $stmt->bind_param("ii", $karyawan_id, $tahun);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode(['success' => true, 'sisa_jatah' => $result['sisa_jatah'] ?? 12]);
        } elseif ($action === 'get_calendar_events') {
            $start = $_GET['start'];
            $end = $_GET['end'];
            $divisi_id = !empty($_GET['divisi_id']) ? (int)$_GET['divisi_id'] : null;

            $sql = "
                SELECT
                    pc.id, 
                    pc.karyawan_id,
                    pc.jenis_cuti_id,
                    k.nama_lengkap as title, 
                    k.nama_lengkap as employee_name,
                    pc.tanggal_mulai as start, 
                    pc.tanggal_selesai as end_date,
                    jc.nama_jenis,
                    pc.keterangan,
                    pc.jumlah_hari
                FROM hr_pengajuan_cuti pc
                JOIN hr_karyawan k ON pc.karyawan_id = k.id
                JOIN hr_jenis_cuti jc ON pc.jenis_cuti_id = jc.id
                WHERE pc.status = 'approved' AND pc.tanggal_mulai <= ? AND pc.tanggal_selesai >= ?";
            
            $params = [$end, $start];
            $types = "ss";

            if ($divisi_id) {
                $sql .= " AND k.divisi_id = ?";
                $params[] = $divisi_id;
                $types .= "i";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Adjust end date for FullCalendar all-day events
            foreach ($events as &$event) {
                $endDate = new DateTime($event['end_date']);
                $endDate->modify('+1 day');
                $event['end'] = $endDate->format('Y-m-d');
                
                // Assign color based on type
                $jenis = strtolower($event['nama_jenis']);
                if (strpos($jenis, 'tahunan') !== false) {
                    $event['backgroundColor'] = '#3B82F6'; // Blue-500
                    $event['borderColor'] = '#3B82F6';
                } elseif (strpos($jenis, 'sakit') !== false) {
                    $event['backgroundColor'] = '#10B981'; // Emerald-500
                    $event['borderColor'] = '#10B981';
                } elseif (strpos($jenis, 'melahirkan') !== false) {
                    $event['backgroundColor'] = '#8B5CF6'; // Violet-500
                    $event['borderColor'] = '#8B5CF6';
                } else {
                    $event['backgroundColor'] = '#F59E0B'; // Amber-500
                    $event['borderColor'] = '#F59E0B';
                }

                $event['title'] = $event['title'] . ' (' . $event['nama_jenis'] . ')';
                unset($event['end_date']); // Clean up
            }

            echo json_encode($events);
        } elseif ($action === 'get_quota_list') {
            $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
            
            // Ambil data karyawan aktif beserta jatah cutinya untuk tahun yang dipilih
            // Default jatah 12 jika belum di-set
            $sql = "SELECT k.id, k.nip, k.nama_lengkap, d.nama_divisi, 
                           COALESCE(jc.jatah_awal, 12) as jatah_awal, 
                           COALESCE(jc.sisa_jatah, 12) as sisa_jatah,
                           (SELECT COALESCE(SUM(pc.jumlah_hari), 0) 
                            FROM hr_pengajuan_cuti pc 
                            JOIN hr_jenis_cuti jcuti ON pc.jenis_cuti_id = jcuti.id
                            WHERE pc.karyawan_id = k.id 
                              AND pc.status = 'pending' 
                              AND YEAR(pc.tanggal_mulai) = ?
                              AND jcuti.mengurangi_jatah_cuti = 1
                           ) as cuti_pending
                    FROM hr_karyawan k 
                    LEFT JOIN hr_divisi d ON k.divisi_id = d.id
                    LEFT JOIN hr_jatah_cuti jc ON k.id = jc.karyawan_id AND jc.tahun = ?
                    WHERE k.status = 'aktif'
                    ORDER BY k.nama_lengkap ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $tahun, $tahun);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $data]);
        } elseif ($action === 'get_pending_count') {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hr_pengajuan_cuti WHERE status = 'pending'");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            echo json_encode(['success' => true, 'total' => $result['total']]);
        } else { // list
            $sql = "SELECT pc.*, k.nama_lengkap, jc.nama_jenis FROM hr_pengajuan_cuti pc JOIN hr_karyawan k ON pc.karyawan_id = k.id JOIN hr_jenis_cuti jc ON pc.jenis_cuti_id = jc.id WHERE 1=1";
            $params = [];
            $types = "";

            // Jika bukan admin, hanya tampilkan data sendiri
            if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
                $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
                $stmt_k->bind_param("i", $_SESSION['user_id']);
                $stmt_k->execute();
                $res_k = $stmt_k->get_result();
                if ($row_k = $res_k->fetch_assoc()) {
                    $sql .= " AND pc.karyawan_id = ?";
                    $params[] = $row_k['id'];
                    $types .= "i";
                }
            } elseif (!empty($_GET['karyawan_id'])) { $sql .= " AND pc.karyawan_id = ?"; $params[] = (int)$_GET['karyawan_id']; $types .= "i"; }

            if (!empty($_GET['bulan'])) { $sql .= " AND MONTH(pc.tanggal_mulai) = ?"; $params[] = (int)$_GET['bulan']; $types .= "i"; }
            if (!empty($_GET['tahun'])) { $sql .= " AND YEAR(pc.tanggal_mulai) = ?"; $params[] = (int)$_GET['tahun']; $types .= "i"; }

            $sql .= " ORDER BY pc.tanggal_mulai DESC";
            $stmt = $conn->prepare($sql);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }
    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $karyawan_id = isset($_POST['karyawan_id']) ? (int)$_POST['karyawan_id'] : null;
            $jenis_cuti_id = (int)$_POST['jenis_cuti_id'];
            $tanggal_mulai = $_POST['tanggal_mulai'];
            $tanggal_selesai = $_POST['tanggal_selesai'];
            $keterangan = $_POST['keterangan'];

            if ($id && empty($karyawan_id)) {
                 // Jika edit tapi karyawan_id kosong (misal disabled field), ambil dari DB
                 $karyawan_id = $conn->query("SELECT karyawan_id FROM hr_pengajuan_cuti WHERE id = $id")->fetch_assoc()['karyawan_id'];
            }
            // Jika user biasa, paksa ID karyawan sendiri
            if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
                $stmt_k = $conn->prepare("SELECT id FROM hr_karyawan WHERE user_id = ?");
                $stmt_k->bind_param("i", $_SESSION['user_id']);
                $stmt_k->execute();
                $res_k = $stmt_k->get_result();
                if ($row_k = $res_k->fetch_assoc()) {
                    $karyawan_id = $row_k['id'];
                } else { throw new Exception("Data karyawan Anda tidak ditemukan."); }
            }

            if ($tanggal_selesai < $tanggal_mulai) throw new Exception("Tanggal selesai tidak boleh sebelum tanggal mulai.");

            $start = new DateTime($tanggal_mulai);
            $end = new DateTime($tanggal_selesai);
            $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($start, $interval, $end);
            $jumlah_hari = 0;
            foreach ($dateRange as $date) {
                // Exclude weekends (Saturday=6, Sunday=7)
                if ($date->format('N') < 6) {
                    $jumlah_hari++;
                }
            }
            if ($jumlah_hari <= 0) throw new Exception("Jumlah hari cuti harus lebih dari 0.");

            // Handle File Upload
            $lampiran_path = null;
            if (isset($_FILES['lampiran_file']) && $_FILES['lampiran_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PROJECT_ROOT . '/uploads/cuti/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileName = time() . '_' . basename($_FILES['lampiran_file']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['lampiran_file']['tmp_name'], $targetPath)) {
                    $lampiran_path = 'uploads/cuti/' . $fileName;
                }
            }

            $conn->begin_transaction();
            try {
                // --- LOGIKA UPDATE: Revert efek lama jika status Approved ---
                if ($id) {
                    $stmt_old = $conn->prepare("SELECT * FROM hr_pengajuan_cuti WHERE id = ? FOR UPDATE");
                    $stmt_old->bind_param("i", $id);
                    $stmt_old->execute();
                    $old_data = $stmt_old->get_result()->fetch_assoc();

                    if ($old_data && $old_data['status'] === 'approved') {
                        // 1. Kembalikan Jatah Cuti Lama
                        $stmt_jenis_old = $conn->prepare("SELECT mengurangi_jatah_cuti FROM hr_jenis_cuti WHERE id = ?");
                        $stmt_jenis_old->bind_param("i", $old_data['jenis_cuti_id']);
                        $stmt_jenis_old->execute();
                        $mengurangi_old = $stmt_jenis_old->get_result()->fetch_assoc()['mengurangi_jatah_cuti'] ?? 0;

                        if ($mengurangi_old) {
                            $tahun_old = date('Y', strtotime($old_data['tanggal_mulai']));
                            $conn->query("UPDATE hr_jatah_cuti SET sisa_jatah = sisa_jatah + {$old_data['jumlah_hari']} WHERE karyawan_id = {$old_data['karyawan_id']} AND tahun = $tahun_old");
                        }

                        // 2. Hapus Absensi Lama (Hapus berdasarkan range tanggal dan karyawan)
                        $stmt_del_abs = $conn->prepare("DELETE FROM hr_absensi WHERE karyawan_id = ? AND tanggal BETWEEN ? AND ? AND status = 'izin'");
                        $stmt_del_abs->bind_param("iss", $old_data['karyawan_id'], $old_data['tanggal_mulai'], $old_data['tanggal_selesai']);
                        $stmt_del_abs->execute();
                    }
                }
                // ------------------------------------------------------------

                // Cek apakah jenis cuti mengurangi jatah
                $stmt_jenis = $conn->prepare("SELECT mengurangi_jatah_cuti FROM hr_jenis_cuti WHERE id = ?");
                $stmt_jenis->bind_param("i", $jenis_cuti_id);
                $stmt_jenis->execute();
                $mengurangi_jatah = $stmt_jenis->get_result()->fetch_assoc()['mengurangi_jatah_cuti'] ?? 0;

                if ($mengurangi_jatah) {
                    $tahun_cuti = date('Y', strtotime($tanggal_mulai));
                    // Get or create jatah cuti
                    $stmt_jatah = $conn->prepare("SELECT id, sisa_jatah FROM hr_jatah_cuti WHERE karyawan_id = ? AND tahun = ? FOR UPDATE");
                    $stmt_jatah->bind_param("ii", $karyawan_id, $tahun_cuti);
                    $stmt_jatah->execute();
                    $jatah = $stmt_jatah->get_result()->fetch_assoc();

                    if (!$jatah) { // Jika belum ada, buatkan
                        $conn->query("INSERT INTO hr_jatah_cuti (karyawan_id, tahun, jatah_awal, sisa_jatah) VALUES ($karyawan_id, $tahun_cuti, 12, 12)");
                        $sisa_jatah = 12;
                    } else {
                        $sisa_jatah = $jatah['sisa_jatah'];
                    }

                    if ($sisa_jatah < $jumlah_hari) throw new Exception("Sisa jatah cuti tidak mencukupi ({$sisa_jatah} hari).");

                    // Kurangi jatah
                    $sisa_baru = $sisa_jatah - $jumlah_hari;
                    $conn->query("UPDATE hr_jatah_cuti SET sisa_jatah = $sisa_baru WHERE karyawan_id = $karyawan_id AND tahun = $tahun_cuti");
                }

                if ($id) {
                    // Update Data
                    if ($lampiran_path) {
                        $stmt = $conn->prepare("UPDATE hr_pengajuan_cuti SET karyawan_id=?, jenis_cuti_id=?, tanggal_mulai=?, tanggal_selesai=?, jumlah_hari=?, keterangan=?, lampiran_file=? WHERE id=?");
                        $stmt->bind_param("iississi", $karyawan_id, $jenis_cuti_id, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $keterangan, $lampiran_path, $id);
                    } else {
                        $stmt = $conn->prepare("UPDATE hr_pengajuan_cuti SET karyawan_id=?, jenis_cuti_id=?, tanggal_mulai=?, tanggal_selesai=?, jumlah_hari=?, keterangan=? WHERE id=?");
                        $stmt->bind_param("iissisi", $karyawan_id, $jenis_cuti_id, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $keterangan, $id);
                    }
                    $stmt->execute();
                    
                    // Jika status sebelumnya approved, buat ulang absensi baru
                    if (isset($old_data) && $old_data['status'] === 'approved') {
                        $stmt_absensi = $conn->prepare("INSERT INTO hr_absensi (karyawan_id, tanggal, status, keterangan) VALUES (?, ?, 'izin', ?)");
                        $keterangan_absensi = "Cuti Disetujui: " . $keterangan;
                        foreach ($dateRange as $date) {
                            if ($date->format('N') < 6) {
                                $tanggal_str = $date->format('Y-m-d');
                                $stmt_absensi->bind_param("iss", $karyawan_id, $tanggal_str, $keterangan_absensi);
                                $stmt_absensi->execute();
                            }
                        }
                    }
                    
                    $msg = 'Data cuti berhasil diperbarui.';
                } else {
                    // Insert Data Baru
                    // Cari atasan untuk approval pertama
                    $stmt_atasan = $conn->prepare("SELECT atasan_id FROM hr_karyawan WHERE id = ?");
                    $stmt_atasan->bind_param("i", $karyawan_id);
                    $stmt_atasan->execute();
                    $atasan_karyawan_id = $stmt_atasan->get_result()->fetch_assoc()['atasan_id'] ?? null;
                    
                    $next_approver_id = null;
                    if ($atasan_karyawan_id) {
                        $next_approver_id = $conn->query("SELECT user_id FROM hr_karyawan WHERE id = $atasan_karyawan_id")->fetch_assoc()['user_id'] ?? null;
                    }

                    $stmt = $conn->prepare("INSERT INTO hr_pengajuan_cuti (karyawan_id, jenis_cuti_id, tanggal_mulai, tanggal_selesai, jumlah_hari, keterangan, next_approver_id, lampiran_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iissisis", $karyawan_id, $jenis_cuti_id, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $keterangan, $next_approver_id, $lampiran_path);
                    $stmt->execute();
                    $msg = 'Pengajuan cuti berhasil disimpan.';
                }

                $conn->commit();
                echo json_encode(['success' => true, 'message' => $msg]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }

        } elseif ($action === 'update_status') {
            $id = (int)$_POST['id'];
            $status = $_POST['status']; // 'approved' or 'rejected'
            $user_id_approver = $_SESSION['user_id'];

            $conn->begin_transaction();
            try {
                $stmt_pengajuan = $conn->prepare("SELECT * FROM hr_pengajuan_cuti WHERE id = ? FOR UPDATE");
                $stmt_pengajuan->bind_param("i", $id);
                $stmt_pengajuan->execute();
                $pengajuan = $stmt_pengajuan->get_result()->fetch_assoc();

                if (!$pengajuan || $pengajuan['status'] !== 'pending' || $pengajuan['next_approver_id'] != $user_id_approver) {
                    throw new Exception("Anda tidak memiliki wewenang untuk memproses pengajuan ini atau pengajuan sudah diproses.");
                }

                // Update approver list
                $approved_by_list = json_decode($pengajuan['approved_by'] ?? '[]', true);
                $approved_by_list[] = $user_id_approver;
                $approved_by_json = json_encode($approved_by_list);

                // Cari atasan dari approver saat ini untuk approval selanjutnya
                $stmt_next = $conn->prepare("SELECT k.atasan_id FROM hr_karyawan k JOIN users u ON k.user_id = u.id WHERE u.id = ?");
                $stmt_next->bind_param("i", $user_id_approver);
                $stmt_next->execute();
                $atasan_approver_id = $stmt_next->get_result()->fetch_assoc()['atasan_id'] ?? null;

                $next_approver_id = null;
                if ($atasan_approver_id) {
                    $next_approver_id = $conn->query("SELECT user_id FROM hr_karyawan WHERE id = $atasan_approver_id")->fetch_assoc()['user_id'] ?? null;
                }

                // Jika sudah tidak ada atasan lagi, status final adalah 'approved'
                $final_status = ($status === 'approved' && $next_approver_id === null) ? 'approved' : $status;

                $stmt_update = $conn->prepare("UPDATE hr_pengajuan_cuti SET status = ?, approved_by = ?, next_approver_id = ?, approved_at = NOW() WHERE id = ?");
                $stmt_update->bind_param("ssii", $final_status, $approved_by_json, $next_approver_id, $id);
                $stmt_update->execute();

                // Jika ditolak, kembalikan jatah cuti
                if ($status === 'rejected') {
                    $stmt_jenis = $conn->prepare("SELECT mengurangi_jatah_cuti FROM hr_jenis_cuti WHERE id = ?");
                    $stmt_jenis->bind_param("i", $pengajuan['jenis_cuti_id']);
                    $stmt_jenis->execute();
                    $mengurangi_jatah = $stmt_jenis->get_result()->fetch_assoc()['mengurangi_jatah_cuti'] ?? 0;

                    if ($mengurangi_jatah) {
                        $tahun_cuti = date('Y', strtotime($pengajuan['tanggal_mulai']));
                        $conn->query("UPDATE hr_jatah_cuti SET sisa_jatah = sisa_jatah + {$pengajuan['jumlah_hari']} WHERE karyawan_id = {$pengajuan['karyawan_id']} AND tahun = $tahun_cuti");
                    }
                }
                // Jika disetujui, masukkan ke tabel absensi
                elseif ($final_status === 'approved') {
                    $stmt_absensi = $conn->prepare("INSERT INTO hr_absensi (karyawan_id, tanggal, status, keterangan) VALUES (?, ?, 'izin', ?)");
                    $start = new DateTime($pengajuan['tanggal_mulai']);
                    $end = new DateTime($pengajuan['tanggal_selesai']);
                    $end->modify('+1 day');
                    $interval = new DateInterval('P1D');
                    $dateRange = new DatePeriod($start, $interval, $end);
                    $keterangan_absensi = "Cuti Disetujui: " . $pengajuan['keterangan'];

                    foreach ($dateRange as $date) {
                        if ($date->format('N') < 6) { // Hanya insert untuk hari kerja
                            $tanggal_str = $date->format('Y-m-d');
                            $stmt_absensi->bind_param("iss", $pengajuan['karyawan_id'], $tanggal_str, $keterangan_absensi);
                            $stmt_absensi->execute();
                        }
                    }
                }

                $conn->commit();
                $msg = "Status pengajuan berhasil diubah menjadi '{$status}'.";
                if ($status === 'approved' && $next_approver_id) {
                    $msg .= " Menunggu persetujuan atasan selanjutnya.";
                }
                echo json_encode(['success' => true, 'message' => $msg]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }

        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            // Logika untuk mengembalikan jatah cuti jika pengajuan yang dihapus adalah cuti tahunan
            // Untuk simplifikasi, saat ini hanya menghapus pengajuan.
            $stmt = $conn->prepare("DELETE FROM hr_pengajuan_cuti WHERE id = ? AND status = 'pending'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Pengajuan cuti berhasil dihapus.']);
            } else {
                throw new Exception("Gagal menghapus pengajuan. Mungkin sudah diproses.");
            }

        } elseif ($action === 'set_jatah_cuti') {
            $set_for_all = isset($_POST['set_for_all']) && $_POST['set_for_all'] == '1';
            $karyawan_id = (int)($_POST['karyawan_id'] ?? 0);
            $tahun = (int)$_POST['tahun'];
            $jatah_awal = (int)$_POST['jatah_awal'];

            if ($tahun <= 2000 || $jatah_awal < 0) throw new Exception("Tahun atau jumlah jatah tidak valid.");

            $karyawan_ids = [];
            if ($set_for_all) {
                $res = $conn->query("SELECT id FROM hr_karyawan WHERE status = 'aktif'");
                while ($row = $res->fetch_assoc()) {
                    $karyawan_ids[] = $row['id'];
                }
            } else {
                if ($karyawan_id <= 0) throw new Exception("Silakan pilih karyawan.");
                $karyawan_ids[] = $karyawan_id;
            }

            $conn->begin_transaction();
            try {
                $stmt_terpakai = $conn->prepare("
                    SELECT COALESCE(SUM(pc.jumlah_hari), 0) as total_terpakai 
                    FROM hr_pengajuan_cuti pc
                    JOIN hr_jenis_cuti jc ON pc.jenis_cuti_id = jc.id
                    WHERE pc.karyawan_id = ? AND YEAR(pc.tanggal_mulai) = ? AND pc.status = 'approved' AND jc.mengurangi_jatah_cuti = 1
                ");
                $stmt_upsert = $conn->prepare("
                    INSERT INTO hr_jatah_cuti (karyawan_id, tahun, jatah_awal, sisa_jatah) VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE jatah_awal = VALUES(jatah_awal), sisa_jatah = VALUES(sisa_jatah)
                ");

                foreach ($karyawan_ids as $kid) {
                    $stmt_terpakai->bind_param("ii", $kid, $tahun);
                    $stmt_terpakai->execute();
                    $terpakai = $stmt_terpakai->get_result()->fetch_assoc()['total_terpakai'] ?? 0;
                    $sisa_jatah = $jatah_awal - $terpakai;

                    $stmt_upsert->bind_param("iiii", $kid, $tahun, $jatah_awal, $sisa_jatah);
                    $stmt_upsert->execute();
                }
                $conn->commit();
                $message = $set_for_all ? "Jatah cuti untuk " . count($karyawan_ids) . " karyawan aktif berhasil diatur." : "Jatah cuti berhasil diatur.";
                echo json_encode(['success' => true, 'message' => $message]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}