<?php  
// Aplikasi RT - Front Controller

// Mulai sesi di setiap permintaan. Ini harus dilakukan sebelum output apa pun.
session_start();  

// --- HTML Minification (Membuat Source Code 1 Baris) ---
ob_start(function($buffer) {
    // 1. Hapus komentar HTML (kecuali conditional comments IE)
    $buffer = preg_replace('/<!--(?!(?:\[if|<!))(.|\s)*?-->/', '', $buffer);
    
    // 2. Hapus whitespace (spasi/newline) di antara tag HTML
    // Mengubah ">   <" menjadi "><"
    $buffer = preg_replace('/>\s+</', '><', $buffer);
    
    // 3. Hapus whitespace berlebih menjadi satu spasi (Opsional, hati-hati dengan <pre> atau JS inline)
    // $buffer = preg_replace('/\s+/', ' ', $buffer); 
    
    return trim($buffer);
});

// Muat komponen inti
require_once 'includes/bootstrap.php';

// --- Auto Login from "Remember Me" Cookie ---
// Jalankan ini setelah bootstrap (untuk fungsi) tetapi sebelum router (untuk otentikasi)
if (empty($_SESSION['loggedin']) && isset($_COOKIE['remember_me'])) {
    list($selector, $validator) = explode(':', $_COOKIE['remember_me'], 2);

    if (!empty($selector) && !empty($validator)) {
        // Fungsi attempt_login_with_cookie() didefinisikan di dalam bootstrap.php
        attempt_login_with_cookie($selector, $validator);
    }
}
// --- End Auto Login ---

require_once 'includes/Router.php';

// Router membutuhkan base path yang sudah didefinisikan di bootstrap.php
$router = new Router(BASE_PATH);

// --- Definisikan Rute (Routes) ---

// Rute untuk tamu (hanya bisa diakses jika belum login)
$router->get('/login', 'login.php', ['guest']);
$router->post('/login', 'actions/auth.php'); // Handler untuk proses login
$router->get('/forgot', 'pages/forgot_password.php', ['guest']);
$router->post('/actions/forgot_password_action.php', 'actions/forgot_password_action.php', ['guest']);
$router->get('/reset-password', 'pages/reset_password.php', ['guest']);
$router->post('/reset-password', 'actions/reset_password_action.php', ['guest']);

// Rute yang memerlukan otentikasi
$router->get('/', function() {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        // Cek Role: Jika Karyawan (ID 4), arahkan ke Portal
        if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4) {
            header('Location: ' . base_url('/hr/portal/dashboard'));
        } else {
            header('Location: ' . base_url('/dashboard'));
        }
    } else {
        header('Location: ' . base_url('/login'));
    }
    exit;
});
$router->get('/dashboard', function() {
    // Proteksi: Karyawan tidak boleh akses dashboard utama
    if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4) {
        header('Location: ' . base_url('/hr/portal/dashboard'));
        exit;
    }
    require 'pages/dashboard.php';
}, ['auth']);
$router->get('/buku-panduan', 'pages/buku_panduan.php', ['auth']);
$router->get('/logout', 'logout.php');
$router->get('/my-profile/change-password', 'pages/my_profile.php', ['auth']);

// --- Rute Utama Aplikasi Keuangan ---
$router->get('/transaksi', 'pages/transaksi.php', ['auth']);
$router->get('/pembelian', 'pages/pembelian.php', ['auth']);
$router->get('/penjualan', 'pages/penjualan.php', ['auth']); // Rute baru untuk halaman penjualan
$router->get('/stok', 'pages/stok.php', ['auth']);
$router->get('/stok-opname', 'pages/stok_opname.php', ['auth']);
$router->get('/daftar-jurnal', 'pages/daftar_jurnal.php', ['auth']);
$router->get('/konsinyasi', 'pages/konsinyasi.php', ['auth']);
$router->get('/transaksi-berulang', 'pages/transaksi_berulang.php', ['auth']);
$router->get('/rekonsiliasi-bank', 'pages/rekonsiliasi_bank.php', ['auth']);
$router->get('/histori-rekonsiliasi', 'pages/histori_rekonsiliasi.php', ['auth']);
$router->get('/aset-tetap', 'pages/aset_tetap.php', ['auth']);
$router->get('/entri-jurnal', 'pages/entri_jurnal.php', ['auth']);
$router->get('/coa', 'pages/coa.php', ['auth']);
$router->get('/saldo-awal', 'pages/saldo_awal.php', ['auth']);
$router->get('/laporan', 'pages/laporan.php', ['auth']); 
$router->get('/laporan-stok', 'pages/laporan_stok.php', ['auth']);
$router->get('/laporan-penjualan', 'pages/laporan_penjualan.php', ['auth']);
$router->get('/laporan-penjualan-item', 'pages/laporan_penjualan_item.php', ['auth']);
$router->get('/laporan-kartu-stok', 'pages/laporan_kartu_stok.php', ['auth']);
$router->get('/laporan-persediaan', 'pages/laporan_persediaan.php', ['auth']);
$router->get('/laporan-pertumbuhan-persediaan', 'pages/laporan_pertumbuhan_persediaan.php', ['auth']);
$router->get('/anggaran', 'pages/anggaran.php', ['auth']);
$router->get('/neraca-saldo', 'pages/neraca_saldo.php', ['auth']);
$router->get('/tutup-buku', 'pages/tutup_buku.php', ['auth', 'admin']);
$router->get('/laporan-laba-ditahan', 'pages/laporan_laba_ditahan.php', ['auth']);
$router->get('/laporan-pertumbuhan-laba', 'pages/laporan_pertumbuhan_laba.php', ['auth']);
$router->get('/analisis-rasio', 'pages/laporan_analisis_rasio.php', ['auth']); // Nama file halaman sudah benar
$router->get('/activity-log', 'pages/activity_log.php', ['auth', 'admin']);
$router->get('/laporan-harian', 'pages/laporan_harian.php', ['auth']);
$router->get('/buku-besar', 'pages/buku_besar.php', ['auth']);
$router->get('/settings', 'pages/settings.php', ['auth']);
$router->get('/users', 'pages/users.php', ['auth', 'admin']); // Halaman manajemen pengguna
$router->get('/roles', 'pages/roles.php', ['auth', 'admin']);
$router->post('/roles', 'pages/roles.php', ['auth', 'admin']);

// Maintenance
$router->get('/maintenance', 'pages/maintenance.php', ['auth']);
$router->post('/actions/toggle_maintenance', 'actions/toggle_maintenance.php', ['auth', 'admin']);

// --- Rute Modul HR & Payroll ---
$router->get('/hr/dashboard', 'pages/hr/dashboard.php', ['auth']);
$router->get('/hr/karyawan', 'pages/hr/karyawan.php', ['auth']);
$router->get('/hr/jabatan', 'pages/hr/jabatan.php', ['auth']);
$router->get('/hr/divisi', 'pages/hr/divisi.php', ['auth']);
$router->get('/hr/struktur-organisasi', 'pages/hr/struktur_organisasi.php', ['auth']);
$router->get('/hr/master-dashboard', 'pages/hr/master_dashboard.php', ['auth']);
$router->get('/hr/kantor', 'pages/hr/kantor.php', ['auth']);
$router->get('/hr/golongan-absensi', 'pages/hr/golonganabsensi.php', ['auth']);
$router->get('/hr/jadwal-kerja', 'pages/hr/jadwalkerja.php', ['auth']);
$router->get('/hr/status-absensi', 'pages/hr/statusabsensi.php', ['auth']);
$router->get('/hr/absensi', 'pages/hr/absensi.php', ['auth']);
$router->get('/hr/jenis-cuti', 'pages/hr/jeniscuti.php', ['auth']);
$router->get('/hr/manajemen-cuti', 'pages/hr/manajemencuti.php', ['auth']);
$router->get('/hr/kalender-cuti', 'pages/hr/kalendercuti.php', ['auth']);
$router->get('/hr/komponen-gaji', 'pages/hr/komponengaji.php', ['auth']);
$router->get('/hr/golongan-gaji', 'pages/hr/golongangaji.php', ['auth']);
$router->get('/hr/penggajian', 'pages/hr/penggajian.php', ['auth']);
$router->get('/hr/payroll-dashboard', 'pages/hr/payroll_dashboard.php', ['auth']);
$router->get('/hr/laporan', 'pages/hr/laporan.php', ['auth']);
$router->get('/hr/pengaturan-pajak', 'pages/hr/pengaturan_pajak.php', ['auth']);
$router->get('/hr/manajemen-klaim', 'pages/hr/manajemen_klaim.php', ['auth']);
$router->get('/hr/lembur', 'pages/hr/lembur.php', ['auth']);
$router->get('/hr/peringatan-kontrak', 'pages/hr/peringatan_kontrak.php', ['auth']);
$router->get('/hr/kpi-templates', 'pages/hr/kpi_templates.php', ['auth']);
$router->get('/hr/penilaian-kinerja', 'pages/hr/penilaian_kinerja.php', ['auth']);
$router->get('/hr/pengumuman', 'pages/hr/pengumuman.php', ['auth']);
$router->get('/hr/rekrutmen', 'pages/hr/rekrutmen.php', ['auth']);
$router->get('/hr/offboarding', 'pages/hr/offboarding.php', ['auth']);

// --- Rute Portal Karyawan ---
$router->get('/hr/portal/dashboard', 'pages/hr/portal/dashboard.php', ['auth']);
$router->get('/hr/portal/profil', 'pages/hr/portal/profil.php', ['auth']);
$router->get('/hr/portal/absensi', 'pages/hr/portal/absensi.php', ['auth']);
$router->get('/hr/portal/pengajuan-cuti', 'pages/hr/portal/pengajuan_cuti.php', ['auth']);
$router->get('/hr/portal/klaim', 'pages/hr/portal/klaim.php', ['auth']);
$router->get('/hr/portal/slip-gaji', 'pages/hr/portal/slipgaji.php', ['auth']);

// --- Rute API (Untuk proses data via AJAX) ---
// Rute ini akan dipanggil oleh JavaScript untuk mendapatkan, menambah, mengubah, dan menghapus data tanpa reload halaman.
$router->get('/api/dashboard', 'api/dashboard_handler.php', ['auth']); // Mengambil data untuk dashboard

// API untuk Transaksi
$router->get('/api/transaksi', 'api/transaksi_handler.php', ['auth']);
$router->post('/api/transaksi', 'api/transaksi_handler.php', ['auth']);

// API untuk Pembelian
$router->get('/api/pembelian', 'api/pembelian_handler.php', ['auth']);
$router->post('/api/pembelian', 'api/pembelian_handler.php', ['auth']);

$router->get('/api/laporan-penjualan', 'api/laporan_penjualan_handler.php', ['auth']);
$router->get('/api/laporan-penjualan-item', 'api/laporan_penjualan_item_handler.php', ['auth']);
// API untuk Penjualan
$router->get('/api/penjualan', 'api/penjualan_handler.php', ['auth']);
$router->post('/api/penjualan', 'api/penjualan_handler.php', ['auth']);

// API untuk Barang & Stok
$router->get('/api/stok', 'api/stok_handler.php', ['auth']);
$router->post('/api/stok', 'api/stok_handler.php', ['auth']);

// API untuk fitur lainnya (Rekening, Kategori, Anggaran)
$router->get('/api/coa', 'api/coa_handler.php', ['auth']);
$router->post('/api/coa', 'api/coa_handler.php', ['auth']);
$router->get('/api/laporan/neraca', 'api/laporan_neraca_handler.php', ['auth']);
$router->get('/api/laporan/laba-rugi', 'api/laporan_laba_rugi_handler.php', ['auth']);
$router->get('/api/laporan-harian', 'api/laporan_harian_handler.php', ['auth']);
$router->get('/api/pertumbuhan_persediaan', 'api/pertumbuhan_persediaan.php', ['auth']);
$router->get('/api/laporan_stok', 'api/laporan_stok_handler.php', ['auth']);
$router->get('/api/csv', 'api/laporan_cetak_csv_handler.php', ['auth']); // Rute baru untuk cetak CSV
$router->get('/api/pdf', 'api/laporan_cetak_handler.php', ['auth']); // Rute baru untuk cetak PDF (GET)
$router->post('/api/pdf', 'api/laporan_cetak_handler.php', ['auth']); // Rute baru untuk cetak PDF (POST)
$router->get('/api/saldo-awal', 'api/saldo_awal_handler.php', ['auth']);
$router->post('/api/saldo-awal', 'api/saldo_awal_handler.php', ['auth']);
$router->get('/api/buku-besar-data', 'api/buku_besar_data_handler.php', ['auth']);
$router->get('/api/entri-jurnal', 'api/entri_jurnal_handler.php', ['auth']);
$router->get('/api/laporan/arus-kas', 'api/laporan_arus_kas_handler.php', ['auth']);
$router->post('/api/entri-jurnal', 'api/entri_jurnal_handler.php', ['auth']);

$router->get('/api/neraca-saldo', 'api/neraca_saldo_handler.php', ['auth']);
$router->get('/api/konsinyasi', 'api/konsinyasi_handler.php', ['auth']);
$router->post('/api/konsinyasi', 'api/konsinyasi_handler.php', ['auth']);

$router->get('/api/recurring', 'api/recurring_handler.php', ['auth']);
$router->post('/api/recurring', 'api/recurring_handler.php', ['auth']);

// API untuk Rekonsiliasi Bank
$router->get('/api/rekonsiliasi-bank', 'api/rekonsiliasi_bank_handler.php', ['auth']);
$router->post('/api/rekonsiliasi-bank', 'api/rekonsiliasi_bank_handler.php', ['auth']);
$router->get('/api/histori-rekonsiliasi', 'api/histori_rekonsiliasi_handler.php', ['auth']);
$router->post('/api/histori-rekonsiliasi', 'api/histori_rekonsiliasi_handler.php', ['auth']);

$router->get('/api/tutup-buku', 'api/tutup_buku_handler.php', ['auth', 'admin']);
$router->get('/api/laporan-laba-ditahan', 'api/laporan_laba_ditahan_handler.php', ['auth']);
$router->post('/api/tutup-buku', 'api/tutup_buku_handler.php', ['auth', 'admin']);
$router->get('/api/laporan-pertumbuhan-laba', 'api/laporan_pertumbuhan_laba_handler.php', ['auth']);
$router->get('/api/analisis-rasio', 'api/analisis_rasio_handler.php', ['auth']); // Nama file API sudah benar

$router->get('/api/activity-log', 'api/activity_log_handler.php', ['auth', 'admin']);
$router->get('/api/anggaran', 'api/anggaran_handler.php', ['auth']);
$router->post('/api/anggaran', 'api/anggaran_handler.php', ['auth']);
$router->get('/api/settings', 'api/settings_handler.php', ['auth']);
$router->post('/api/settings', 'api/settings_handler.php', ['auth']);
$router->get('/api/aset_tetap', 'api/aset_tetap_handler.php', ['auth']);
$router->post('/api/aset_tetap', 'api/aset_tetap_handler.php', ['auth']);
$router->get('/api/global-search', 'api/global_search_handler.php', ['auth']); // API untuk pencarian global
$router->get('/api/users', 'api/users_handler.php', ['auth', 'admin']); // API untuk manajemen pengguna
$router->post('/api/users', 'api/users_handler.php', ['auth', 'admin']);
$router->post('/api/my-profile/change-password', 'api/my_profile_handler.php', ['auth']);

// API HR & Payroll
$router->get('/api/hr/karyawan', 'api/hr/karyawan_handler.php', ['auth']);
$router->post('/api/hr/karyawan', 'api/hr/karyawan_handler.php', ['auth']);
$router->get('/api/hr/jabatan', 'api/hr/jabatan_handler.php', ['auth']);
$router->post('/api/hr/jabatan', 'api/hr/jabatan_handler.php', ['auth']);
$router->get('/api/hr/golongan-absensi', 'api/hr/golonganabsensi_handler.php', ['auth']);
$router->post('/api/hr/golongan-absensi', 'api/hr/golonganabsensi_handler.php', ['auth']);
$router->get('/api/hr/kantor', 'api/hr/kantor_handler.php', ['auth']);
$router->post('/api/hr/kantor', 'api/hr/kantor_handler.php', ['auth']);
$router->get('/api/hr/jadwal-kerja', 'api/hr/jadwalkerja_handler.php', ['auth']);
$router->post('/api/hr/jadwal-kerja', 'api/hr/jadwalkerja_handler.php', ['auth']);
$router->get('/api/hr/status-absensi', 'api/hr/statusabsensi_handler.php', ['auth']);
$router->post('/api/hr/status-absensi', 'api/hr/statusabsensi_handler.php', ['auth']);
$router->get('/api/hr/jenis-cuti', 'api/hr/jeniscuti_handler.php', ['auth']);
$router->post('/api/hr/jenis-cuti', 'api/hr/jeniscuti_handler.php', ['auth']);
$router->get('/api/hr/manajemen-cuti', 'api/hr/manajemencuti_handler.php', ['auth']);
$router->post('/api/hr/manajemen-cuti', 'api/hr/manajemencuti_handler.php', ['auth']);
$router->get('/api/hr/komponen-gaji', 'api/hr/komponengaji_handler.php', ['auth']);
$router->post('/api/hr/komponen-gaji', 'api/hr/komponengaji_handler.php', ['auth']);
$router->get('/api/hr/golongan-gaji', 'api/hr/golongangaji_handler.php', ['auth']);
$router->post('/api/hr/golongan-gaji', 'api/hr/golongangaji_handler.php', ['auth']);
$router->get('/api/hr/divisi', 'api/hr/divisi_handler.php', ['auth']);
$router->post('/api/hr/divisi', 'api/hr/divisi_handler.php', ['auth']);
$router->get('/api/hr/master-dashboard', 'api/hr/master_dashboard_handler.php', ['auth']);
$router->get('/api/hr/absensi', 'api/hr/absensi_handler.php', ['auth']);
$router->post('/api/hr/absensi', 'api/hr/absensi_handler.php', ['auth']);
$router->get('/api/hr/penggajian', 'api/hr/penggajian_handler.php', ['auth']);
$router->post('/api/hr/penggajian', 'api/hr/penggajian_handler.php', ['auth']);
$router->get('/api/hr/payroll-dashboard', 'api/hr/payroll_dashboard_handler.php', ['auth']);
$router->get('/api/hr/laporan', 'api/hr/laporan_handler.php', ['auth']);
$router->post('/api/hr/laporan', 'api/hr/laporan_handler.php', ['auth']);
$router->get('/api/hr/pengaturan-pajak', 'api/hr/pengaturan_pajak_handler.php', ['auth']);
$router->post('/api/hr/pengaturan-pajak', 'api/hr/pengaturan_pajak_handler.php', ['auth']);
$router->get('/api/hr/klaim', 'api/hr/klaim_handler.php', ['auth']);
$router->post('/api/hr/klaim', 'api/hr/klaim_handler.php', ['auth']);
$router->get('/api/hr/lembur', 'api/hr/lembur_handler.php', ['auth']);
$router->post('/api/hr/lembur', 'api/hr/lembur_handler.php', ['auth']);
$router->get('/api/hr/dokumen', 'api/hr/dokumen_handler.php', ['auth']);
$router->post('/api/hr/dokumen', 'api/hr/dokumen_handler.php', ['auth']);
$router->get('/api/hr/kpi', 'api/hr/kpi_handler.php', ['auth']);
$router->post('/api/hr/kpi', 'api/hr/kpi_handler.php', ['auth']);
$router->get('/api/hr/pengumuman', 'api/hr/pengumuman_handler.php', ['auth']);
$router->post('/api/hr/pengumuman', 'api/hr/pengumuman_handler.php', ['auth']);
$router->get('/api/hr/rekrutmen', 'api/hr/rekrutmen_handler.php', ['auth']);
$router->post('/api/hr/rekrutmen', 'api/hr/rekrutmen_handler.php', ['auth']);
$router->get('/api/hr/offboarding', 'api/hr/offboarding_handler.php', ['auth']);
$router->post('/api/hr/offboarding', 'api/hr/offboarding_handler.php', ['auth']);

// API Portal Karyawan
$router->get('/api/hr/portal/dashboard', 'api/hr/portal/dashboard_handler.php', ['auth']);
$router->get('/api/hr/portal/profil', 'api/hr/portal/profil_handler.php', ['auth']);
$router->get('/api/hr/portal/absensi', 'api/hr/portal/absensi_handler.php', ['auth']);
$router->get('/api/hr/portal/slip-gaji', 'api/hr/portal/slipgaji_handler.php', ['auth']);
$router->get('/api/hr/portal/klaim', 'api/hr/portal/klaim_handler.php', ['auth']); // Handler khusus portal atau gunakan handler umum dengan filter


// Jalankan router
$router->dispatch();