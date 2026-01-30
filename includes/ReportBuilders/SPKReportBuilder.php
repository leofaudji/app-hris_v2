<?php
require_once __DIR__ . '/ReportBuilderInterface.php';
require_once PROJECT_ROOT . '/includes/phpqrcode/qrlib.php';

class SPKReportBuilder implements ReportBuilderInterface
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
        $pelamar_id = isset($this->params['id']) ? (int)$this->params['id'] : 0;

        // Ambil data pelamar yang sudah di-hire dan data karyawannya (via nama/email karena relasi langsung mungkin belum ada di tabel pelamar)
        // Asumsi: Saat convert_to_employee, data pelamar tidak dihapus.
        // Kita cari data karyawan berdasarkan nama pelamar yang statusnya hired.
        // Idealnya ada kolom karyawan_id di tabel pelamar, tapi untuk skema saat ini kita join manual.
        
        // Cari data pelamar dulu
        $stmt_p = $this->conn->prepare("SELECT * FROM hr_pelamar WHERE id = ?");
        $stmt_p->bind_param('i', $pelamar_id);
        $stmt_p->execute();
        $pelamar = $stmt_p->get_result()->fetch_assoc();

        if (!$pelamar || $pelamar['status'] !== 'hired') {
            throw new Exception("Data pelamar tidak ditemukan atau belum diterima bekerja.");
        }

        // Cari data karyawan yang cocok (berdasarkan nama, karena saat convert nama disalin)
        // Ini pendekatan 'loose coupling' karena tabel pelamar tidak menyimpan ID karyawan yang baru dibuat.
        $stmt_k = $this->conn->prepare("
            SELECT k.*, j.nama_jabatan, d.nama_divisi, gg.gaji_pokok, gg.nama_golongan
            FROM hr_karyawan k
            LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
            LEFT JOIN hr_divisi d ON k.divisi_id = d.id
            LEFT JOIN hr_golongan_gaji gg ON k.golongan_gaji_id = gg.id
            WHERE k.nama_lengkap = ? 
            ORDER BY k.id DESC LIMIT 1
        ");
        $stmt_k->bind_param('s', $pelamar['nama_lengkap']);
        $stmt_k->execute();
        $karyawan = $stmt_k->get_result()->fetch_assoc();

        if (!$karyawan) {
            throw new Exception("Data karyawan terkait tidak ditemukan.");
        }

        $this->pdf->report_title = 'SURAT PERJANJIAN KERJA';
        $this->pdf->report_period = 'Nomor: ' . date('Y/m') . '/HR/SPK/' . str_pad($karyawan['id'], 4, '0', STR_PAD_LEFT);
        $this->pdf->AddPage();

        // --- Watermark CONFIDENTIAL ---
        $this->pdf->SetFont('Helvetica', 'B', 60);
        $this->pdf->SetTextColor(240, 240, 240); // Abu-abu sangat muda
        $watermarkText = 'CONFIDENTIAL';
        $w = $this->pdf->GetStringWidth($watermarkText);
        $this->pdf->Text((210 - $w) / 2, 150, $watermarkText); // Posisi tengah halaman A4
        $this->pdf->SetTextColor(0, 0, 0); // Reset warna hitam

        // --- Tambahkan QR Code Verifikasi ---
        // Menggunakan library phpqrcode lokal
        // Isi QR: Data ringkas untuk mencocokkan fisik dokumen dengan data digital
        $qrContent = "SPK VALID\nNo: " . str_replace('Nomor: ', '', $this->pdf->report_period) . "\nNama: " . $karyawan['nama_lengkap'] . "\nNIP: " . $karyawan['nip'] . "\nJabatan: " . $karyawan['nama_jabatan'];
        
        // Generate QR Code ke file temporary
        $tempDir = sys_get_temp_dir();
        $qrFile = $tempDir . '/spk_qr_' . md5($qrContent) . '.png';
        
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
        $this->pdf->MultiCell(0, 6, "Pada hari ini, " . date('d F Y') . ", bertempat di " . get_setting('app_city', 'Jakarta') . ", telah dibuat dan disepakati perjanjian kerja antara:");
        $this->pdf->Ln(5);

        // Pihak Pertama (Perusahaan)
        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "1. PIHAK PERTAMA (PERUSAHAAN)", 0, 1);
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->SetX(20);
        $this->pdf->Cell(35, 6, 'Nama Perusahaan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, get_setting('app_name', 'Perusahaan Anda'), 0, 1);
        $this->pdf->SetX(20);
        $this->pdf->Cell(35, 6, 'Alamat', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, get_setting('report_header_address', 'Alamat Perusahaan'), 0, 1);
        $this->pdf->Ln(5);

        // Pihak Kedua (Karyawan)
        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "2. PIHAK KEDUA (KARYAWAN)", 0, 1);
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->SetX(20);
        $this->pdf->Cell(35, 6, 'Nama', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $karyawan['nama_lengkap'], 0, 1);
        $this->pdf->SetX(20);
        $this->pdf->Cell(35, 6, 'NIP', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $karyawan['nip'], 0, 1);
        $this->pdf->Ln(5);

        $this->pdf->MultiCell(0, 6, "Kedua belah pihak sepakat untuk mengikatkan diri dalam Perjanjian Kerja dengan ketentuan sebagai berikut:");
        $this->pdf->Ln(5);

        // Pasal-pasal ringkas
        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "PASAL 1: POSISI DAN TUGAS", 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->MultiCell(0, 6, "Pihak Kedua dipekerjakan oleh Pihak Pertama sebagai " . $karyawan['nama_jabatan'] . " di divisi " . $karyawan['nama_divisi'] . ". Pihak Kedua bersedia melaksanakan tugas-tugas dan tanggung jawab yang diberikan oleh Pihak Pertama dengan sebaik-baiknya sesuai dengan standar kinerja yang ditetapkan perusahaan.");
        $this->pdf->Ln(3);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "PASAL 2: MASA KERJA", 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 11);
        $tgl_masuk = date('d F Y', strtotime($karyawan['tanggal_masuk']));
        $masa_kerja_text = "Perjanjian kerja ini berlaku terhitung mulai tanggal " . $tgl_masuk;
        if ($karyawan['status'] === 'kontrak' && $karyawan['tanggal_berakhir_kontrak']) {
            $masa_kerja_text .= " sampai dengan tanggal " . date('d F Y', strtotime($karyawan['tanggal_berakhir_kontrak'])) . ". Perpanjangan kontrak kerja dapat dilakukan berdasarkan kesepakatan kedua belah pihak dan evaluasi kinerja Pihak Kedua.";
        } else {
            $masa_kerja_text .= " untuk waktu yang tidak ditentukan (Karyawan Tetap), dengan masa percobaan (probation) selama 3 (tiga) bulan sejak tanggal masuk.";
        }
        $this->pdf->MultiCell(0, 6, $masa_kerja_text);
        $this->pdf->Ln(3);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "PASAL 3: IMBALAN JASA", 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 11);
        $gaji = number_format($karyawan['gaji_pokok'], 0, ',', '.');
        $this->pdf->MultiCell(0, 6, "Pihak Pertama akan memberikan imbalan jasa berupa gaji pokok sebesar Rp " . $gaji . " per bulan kepada Pihak Kedua. Selain gaji pokok, Pihak Kedua juga berhak mendapatkan tunjangan-tunjangan lain (seperti tunjangan makan, transportasi, dll) sesuai dengan kebijakan dan peraturan perusahaan yang berlaku.");
        $this->pdf->Ln(3);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "PASAL 4: WAKTU KERJA", 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->MultiCell(0, 6, "Pihak Kedua bersedia mengikuti jam kerja yang telah ditetapkan oleh Pihak Pertama, yaitu 8 (delapan) jam sehari atau 40 (empat puluh) jam seminggu. Jadwal kerja spesifik akan diatur sesuai dengan kebutuhan operasional perusahaan dan posisi Pihak Kedua.");
        $this->pdf->Ln(3);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "PASAL 5: KERAHASIAAN", 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->MultiCell(0, 6, "Pihak Kedua wajib menjaga kerahasiaan seluruh data, dokumen, dan informasi milik Pihak Pertama yang diketahuinya selama masa kerja, baik yang bersifat teknis maupun non-teknis. Kewajiban ini tetap berlaku baik selama masa perjanjian kerja maupun setelah perjanjian kerja ini berakhir.");
        $this->pdf->Ln(3);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "PASAL 6: PEMUTUSAN HUBUNGAN KERJA", 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->MultiCell(0, 6, "Perjanjian kerja ini dapat diakhiri oleh salah satu pihak dengan memberikan pemberitahuan tertulis minimal 30 (tiga puluh) hari sebelumnya (one month notice). Pemutusan hubungan kerja juga dapat terjadi jika Pihak Kedua melakukan pelanggaran berat terhadap peraturan perusahaan atau hukum yang berlaku.");
        $this->pdf->Ln(3);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, "PASAL 7: HAK DAN KEWAJIBAN", 0, 1, 'C');
        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->MultiCell(0, 6, "1. Pihak Kedua berhak menerima gaji, tunjangan, dan hak-hak normatif lainnya sesuai dengan kesepakatan dan peraturan perundang-undangan.");
        $this->pdf->MultiCell(0, 6, "2. Pihak Kedua berhak mendapatkan waktu istirahat, cuti tahunan, dan jaminan sosial tenaga kerja sesuai dengan peraturan perusahaan.");
        $this->pdf->MultiCell(0, 6, "3. Pihak Kedua wajib mematuhi segala peraturan, tata tertib, dan kebijakan yang berlaku di lingkungan perusahaan.");
        $this->pdf->MultiCell(0, 6, "4. Pihak Kedua wajib melaksanakan tugas dengan penuh tanggung jawab, kejujuran, dan dedikasi tinggi.");
        $this->pdf->MultiCell(0, 6, "5. Pihak Pertama berhak mengevaluasi kinerja Pihak Kedua secara berkala dan memberikan sanksi jika terjadi pelanggaran.");
        $this->pdf->MultiCell(0, 6, "6. Pihak Pertama wajib membayarkan gaji dan hak-hak karyawan lainnya tepat waktu kepada Pihak Kedua.");
        $this->pdf->Ln(10);

        $this->pdf->MultiCell(0, 6, "Demikian Surat Perjanjian Kerja ini dibuat rangkap 2 (dua) bermeterai cukup dan mempunyai kekuatan hukum yang sama.");

        $this->pdf->signature_date = date('Y-m-d');
        
        // Custom Signature Block for SPK (Side by Side)
        $this->pdf->Ln(15);
        $y = $this->pdf->GetY();
        
        if ($y + 40 > 270) {
            $this->pdf->AddPage();
            $y = $this->pdf->GetY();
        }

        $this->pdf->Cell(95, 6, 'Pihak Pertama,', 0, 0, 'C');
        $this->pdf->Cell(95, 6, 'Pihak Kedua,', 0, 1, 'C');
        $this->pdf->Cell(95, 6, '(Perusahaan)', 0, 0, 'C');
        $this->pdf->Cell(95, 6, '(Karyawan)', 0, 1, 'C');

        $this->pdf->Ln(25);

        $this->pdf->SetFont('Helvetica', 'B', 11);
        $this->pdf->Cell(95, 6, get_setting('hr_manager_name', 'HR Manager'), 0, 0, 'C');
        $this->pdf->Cell(95, 6, $karyawan['nama_lengkap'], 0, 1, 'C');
    }
}