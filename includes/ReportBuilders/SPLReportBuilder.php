<?php
require_once __DIR__ . '/ReportBuilderInterface.php';
require_once PROJECT_ROOT . '/includes/phpqrcode/qrlib.php';

class SPLReportBuilder implements ReportBuilderInterface
{
    private $pdf;
    private $conn;
    private $params;

    public function __construct(PDF $pdf, mysqli $conn, array $params)
    {
        $this->pdf = $pdf;
        $this->conn = $conn;
        $this->params = $params;
    }

    public function build(): void
    {
        $id = isset($this->params['id']) ? (int)$this->params['id'] : 0;

        // Ambil data lembur beserta detail karyawan dan approver
        $sql = "SELECT l.*, k.nama_lengkap, k.nip, j.nama_jabatan, d.nama_divisi, 
                u_app.nama_lengkap as approver_name,
                k_app.nama_lengkap as approver_emp_name,
                j_app.nama_jabatan as approver_position
                FROM hr_lembur l
                JOIN hr_karyawan k ON l.karyawan_id = k.id
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                LEFT JOIN hr_divisi d ON k.divisi_id = d.id
                LEFT JOIN users u_app ON l.approved_by = u_app.id
                LEFT JOIN hr_karyawan k_app ON u_app.id = k_app.user_id
                LEFT JOIN hr_jabatan j_app ON k_app.jabatan_id = j_app.id
                WHERE l.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) {
            $this->pdf->AddPage();
            $this->pdf->SetFont('Helvetica', '', 12);
            $this->pdf->Cell(0, 10, 'Data lembur tidak ditemukan.', 0, 1, 'C');
            return;
        }

        $this->pdf->report_title = 'SURAT PERINTAH LEMBUR';
        $this->pdf->report_period = 'Nomor: SPL/' . date('Y/m', strtotime($data['tanggal'])) . '/' . str_pad($data['id'], 4, '0', STR_PAD_LEFT);
        $this->pdf->AddPage();

        // --- Tambahkan QR Code Verifikasi ---
        // Menggunakan library phpqrcode lokal
        // Isi QR: Data ringkas untuk mencocokkan fisik dokumen dengan data digital
        $qrContent = "SPL VALID\nNo: " . str_replace('Nomor: ', '', $this->pdf->report_period) . "\nNama: " . $data['nama_lengkap'] . "\nNIP: " . $data['nip'];
        
        // Generate QR Code ke file temporary
        $tempDir = sys_get_temp_dir();
        $qrFile = $tempDir . '/spl_qr_' . md5($qrContent) . '.png';
        
        // Suppress deprecated warnings from phpqrcode library (not compatible with PHP 8.2+)
        $current_error_level = error_reporting();
        error_reporting($current_error_level & ~E_DEPRECATED);
        QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 3, 2);
        error_reporting($current_error_level);
        
        // Tampilkan di pojok kanan atas (X=170, Y=35), sesuaikan posisi jika perlu
        $this->pdf->Image($qrFile, 170, 35, 25, 0, 'PNG');
        
        // Hapus file temporary setelah digunakan
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }

        $this->pdf->SetFont('Helvetica', '', 11);

        // Bagian 1: Pemberi Perintah
        $this->pdf->Cell(0, 8, 'Yang bertanda tangan di bawah ini:', 0, 1);
        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Nama', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['approver_emp_name'] ?? $data['approver_name'] ?? 'Admin', 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Jabatan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['approver_position'] ?? 'HR / Manager', 0, 1);

        $this->pdf->Ln(5);

        // Bagian 2: Penerima Perintah
        $this->pdf->Cell(0, 8, 'Memberikan perintah lembur kepada:', 0, 1);
        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Nama', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_lengkap'], 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'NIP', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nip'], 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Jabatan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_jabatan'] ?? '-', 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Divisi', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_divisi'] ?? '-', 0, 1);

        $this->pdf->Ln(5);

        // Bagian 3: Detail Pekerjaan
        $this->pdf->Cell(0, 8, 'Untuk melaksanakan pekerjaan lembur pada:', 0, 1);
        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Hari / Tanggal', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $days[date('w', strtotime($data['tanggal']))];
        $this->pdf->Cell(0, 6, $dayName . ', ' . date('d-m-Y', strtotime($data['tanggal'])), 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Waktu', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, substr($data['jam_mulai'], 0, 5) . ' s/d ' . substr($data['jam_selesai'], 0, 5) . ' WIB', 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Uraian Pekerjaan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->MultiCell(0, 6, $data['keterangan']);

        $this->pdf->Ln(10);
        $this->pdf->MultiCell(0, 6, 'Demikian Surat Perintah Lembur ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.');

        $this->pdf->Ln(15);

        // Bagian 4: Tanda Tangan
        // Cek sisa ruang halaman, jika kurang dari 5cm, buat halaman baru
        if ($this->pdf->GetY() > 220) {
            $this->pdf->AddPage();
        }
        
        $this->pdf->Cell(90, 6, 'Penerima Perintah,', 0, 0, 'C');
        $this->pdf->Cell(90, 6, 'Pemberi Perintah,', 0, 1, 'C');

        $this->pdf->Ln(25);

        $this->pdf->Cell(90, 6, $data['nama_lengkap'], 0, 0, 'C');
        $this->pdf->Cell(90, 6, $data['approver_emp_name'] ?? $data['approver_name'] ?? 'Admin', 0, 1, 'C');
    }
}
