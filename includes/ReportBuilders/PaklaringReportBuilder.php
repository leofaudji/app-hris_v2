<?php
require_once __DIR__ . '/ReportBuilderInterface.php';

class PaklaringReportBuilder implements ReportBuilderInterface
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
        $offboarding_id = isset($this->params['id']) ? (int)$this->params['id'] : 0;

        $sql = "SELECT 
                    k.nama_lengkap, k.nip, k.tanggal_masuk,
                    o.tanggal_efektif,
                    j.nama_jabatan
                FROM hr_offboarding o
                JOIN hr_karyawan k ON o.karyawan_id = k.id
                LEFT JOIN hr_jabatan j ON k.jabatan_id = j.id
                WHERE o.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $offboarding_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) {
            throw new Exception("Data offboarding tidak ditemukan.");
        }

        $this->pdf->report_title = 'SURAT KETERANGAN KERJA';
        $this->pdf->report_period = 'Nomor: ' . date('Y/m') . '/HR/SKK/' . str_pad($offboarding_id, 4, '0', STR_PAD_LEFT);
        $this->pdf->AddPage();

        $this->pdf->SetFont('Helvetica', '', 11);
        $this->pdf->Cell(0, 8, 'Yang bertanda tangan di bawah ini:', 0, 1);
        $this->pdf->Ln(5);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Nama', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, get_setting('hr_manager_name', 'Nama Manajer HR'), 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Jabatan', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, 'HR Manager', 0, 1);
        $this->pdf->Ln(5);

        $this->pdf->Cell(0, 8, 'Dengan ini menerangkan bahwa:', 0, 1);
        $this->pdf->Ln(5);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Nama', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_lengkap'], 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'NIP', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nip'], 0, 1);

        $this->pdf->SetX(20);
        $this->pdf->Cell(40, 6, 'Jabatan Terakhir', 0, 0);
        $this->pdf->Cell(5, 6, ':', 0, 0);
        $this->pdf->Cell(0, 6, $data['nama_jabatan'] ?? '-', 0, 1);
        $this->pdf->Ln(5);

        $tgl_masuk = date('d F Y', strtotime($data['tanggal_masuk']));
        $tgl_keluar = date('d F Y', strtotime($data['tanggal_efektif']));
        $body_text = "Adalah benar telah bekerja di perusahaan kami sejak tanggal {$tgl_masuk} hingga {$tgl_keluar} dengan jabatan terakhir sebagai {$data['nama_jabatan']}.\n\nSelama bekerja, yang bersangkutan telah menunjukkan dedikasi dan kinerja yang baik. Kami mengucapkan terima kasih atas kontribusinya dan semoga sukses di kemudian hari.";
        $this->pdf->MultiCell(0, 6, $body_text);
        $this->pdf->Ln(5);

        $this->pdf->MultiCell(0, 6, 'Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.');

        $this->pdf->signature_date = $data['tanggal_efektif'];
        $this->pdf->RenderSignatureBlock();
    }
}