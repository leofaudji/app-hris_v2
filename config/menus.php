<?php
// Konfigurasi Struktur Menu Aplikasi
// Key harus unik untuk setiap item menu

return [
    ['type' => 'item', 'key' => 'dashboard', 'label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'bi bi-speedometer2'],
    ['type' => 'item', 'key' => 'buku_panduan', 'label' => 'Buku Panduan', 'url' => '/buku-panduan', 'icon' => 'bi bi-question-circle-fill'],
    
    ['type' => 'header', 'label' => 'Aktivitas Utama'],
    
    ['type' => 'collapse', 'key' => 'transaksi', 'label' => 'Transaksi', 'icon' => 'bi bi-pencil-square', 'children' => [
        ['key' => 'penjualan', 'label' => 'Penjualan', 'url' => '/penjualan'],
        ['key' => 'pembelian', 'label' => 'Pembelian', 'url' => '/pembelian'],
        ['key' => 'transaksi_kas', 'label' => 'Transaksi Kas', 'url' => '/transaksi'],
        ['key' => 'entri_jurnal', 'label' => 'Entri Jurnal', 'url' => '/entri-jurnal'],
    ]],
    
    ['type' => 'collapse', 'key' => 'akuntansi', 'label' => 'Akuntansi', 'icon' => 'bi bi-calculator', 'children' => [
        ['key' => 'coa', 'label' => 'Bagan Akun (COA)', 'url' => '/coa'],
        ['key' => 'saldo_awal', 'label' => 'Saldo Awal', 'url' => '/saldo-awal'],
        ['key' => 'anggaran', 'label' => 'Anggaran', 'url' => '/anggaran'],
        ['key' => 'daftar_jurnal', 'label' => 'Daftar Jurnal', 'url' => '/daftar-jurnal'],
        ['key' => 'buku_besar', 'label' => 'Buku Besar', 'url' => '/buku-besar'],
    ]],

    ['type' => 'collapse', 'key' => 'stok', 'label' => 'Stok & Inventaris', 'icon' => 'bi bi-box-seam', 'children' => [
        ['key' => 'barang_stok', 'label' => 'Barang & Stok', 'url' => '/stok'],
        ['key' => 'stok_opname', 'label' => 'Stok Opname', 'url' => '/stok-opname'],
        ['key' => 'laporan_stok', 'label' => 'Laporan Stok', 'url' => '/laporan-stok'],
        ['key' => 'kartu_stok', 'label' => 'Kartu Stok', 'url' => '/laporan-kartu-stok'],
        ['key' => 'nilai_persediaan', 'label' => 'Nilai Persediaan', 'url' => '/laporan-persediaan'],
        ['key' => 'pertumbuhan_persediaan', 'label' => 'Pertumbuhan Persediaan', 'url' => '/laporan-pertumbuhan-persediaan'],
        ['key' => 'aset_tetap', 'label' => 'Aset Tetap', 'url' => '/aset-tetap'],
    ]],

    ['type' => 'header', 'label' => 'HR & Payroll'],
    
    ['type' => 'collapse', 'key' => 'hr_kepegawaian', 'label' => 'Kepegawaian', 'icon' => 'bi bi-people', 'children' => [
        ['key' => 'hr_karyawan', 'label' => 'Data Karyawan', 'url' => '/hr/karyawan'],
        ['key' => 'hr_jabatan', 'label' => 'Jabatan', 'url' => '/hr/jabatan'],
        ['key' => 'hr_divisi', 'label' => 'Divisi', 'url' => '/hr/divisi'],
        ['key' => 'hr_kantor', 'label' => 'Kantor', 'url' => '/hr/kantor'],
        ['key' => 'hr_jadwal_kerja', 'label' => 'Jadwal Kerja', 'url' => '/hr/jadwal-kerja'],
        ['key' => 'hr_golongan_absensi', 'label' => 'Golongan Absensi', 'url' => '/hr/golongan-absensi'],
        ['key' => 'hr_status_absensi', 'label' => 'Status Absensi', 'url' => '/hr/status-absensi'],
        ['key' => 'hr_absensi', 'label' => 'Absensi', 'url' => '/hr/absensi'],
        ['key' => 'hr_jenis_cuti', 'label' => 'Jenis Cuti', 'url' => '/hr/jenis-cuti'],
        ['key' => 'hr_manajemen_cuti', 'label' => 'Manajemen Cuti', 'url' => '/hr/manajemen-cuti'],
        ['key' => 'hr_kalender_cuti', 'label' => 'Kalender Cuti', 'url' => '/hr/kalender-cuti'],
    ]],

    ['type' => 'collapse', 'key' => 'hr_payroll', 'label' => 'Penggajian', 'icon' => 'bi bi-cash-coin', 'children' => [
        ['key' => 'hr_komponen_gaji', 'label' => 'Komponen Gaji', 'url' => '/hr/komponen-gaji'],
        ['key' => 'hr_golongan_gaji', 'label' => 'Golongan Gaji', 'url' => '/hr/golongan-gaji'],
        ['key' => 'hr_penggajian', 'label' => 'Proses Gaji', 'url' => '/hr/penggajian'],
        ['key' => 'hr_laporan', 'label' => 'Laporan Gaji', 'url' => '/hr/laporan'],
        ['key' => 'hr_pengaturan_pajak', 'label' => 'Pengaturan Pajak', 'url' => '/hr/pengaturan-pajak'],
    ]],

    ['type' => 'collapse', 'key' => 'portal_karyawan', 'label' => 'Portal Karyawan', 'icon' => 'bi bi-person-workspace', 'children' => [
        ['key' => 'portal_dashboard', 'label' => 'Dashboard', 'url' => '/portal/dashboard'],
        ['key' => 'portal_profil', 'label' => 'Profil Saya', 'url' => '/portal/profil'],
        ['key' => 'portal_absensi', 'label' => 'Data Absensi', 'url' => '/portal/absensi'],
        ['key' => 'portal_slip_gaji', 'label' => 'Slip Gaji', 'url' => '/portal/slip-gaji'],
    ]],

    ['type' => 'header', 'label' => 'Akuntansi & Laporan'],

    ['type' => 'collapse', 'key' => 'laporan', 'label' => 'Laporan', 'icon' => 'bi bi-bar-chart-line-fill', 'children' => [
        ['key' => 'laporan_harian', 'label' => 'Laporan Harian', 'url' => '/laporan-harian'],
        ['key' => 'penjualan_item', 'label' => 'Penjualan per Item', 'url' => '/laporan-penjualan-item'],
        ['key' => 'laporan_penjualan', 'label' => 'Laporan Penjualan', 'url' => '/laporan-penjualan'],
        ['key' => 'laporan_keuangan', 'label' => 'Laporan Keuangan', 'url' => '/laporan'],
        ['key' => 'neraca_saldo', 'label' => 'Neraca Saldo', 'url' => '/neraca-saldo'],
        ['key' => 'perubahan_laba', 'label' => 'Perubahan Laba', 'url' => '/laporan-laba-ditahan'],
        ['key' => 'pertumbuhan_laba', 'label' => 'Pertumbuhan Laba', 'url' => '/laporan-pertumbuhan-laba'],
        ['key' => 'analisis_rasio', 'label' => 'Analisis Rasio', 'url' => '/analisis-rasio'],
    ]],

    ['type' => 'collapse', 'key' => 'tools', 'label' => 'Alat & Proses', 'icon' => 'bi bi-tools', 'children' => [
        ['key' => 'transaksi_berulang', 'label' => 'Transaksi Berulang', 'url' => '/transaksi-berulang'],
        ['key' => 'rekonsiliasi_bank', 'label' => 'Rekonsiliasi Bank', 'url' => '/rekonsiliasi-bank'],
    ]],

    ['type' => 'header', 'label' => 'Administrasi'],

    ['type' => 'item', 'key' => 'users', 'label' => 'Users', 'url' => '/users', 'icon' => 'bi bi-people-fill'],
    ['type' => 'item', 'key' => 'roles', 'label' => 'Manajemen Role', 'url' => '/roles', 'icon' => 'bi bi-shield-lock-fill'],
    ['type' => 'item', 'key' => 'activity_log', 'label' => 'Log Aktivitas', 'url' => '/activity-log', 'icon' => 'bi bi-list-check'],
    ['type' => 'item', 'key' => 'tutup_buku', 'label' => 'Tutup Buku', 'url' => '/tutup-buku', 'icon' => 'bi bi-archive-fill'],
    ['type' => 'item', 'key' => 'settings', 'label' => 'Pengaturan', 'url' => '/settings', 'icon' => 'bi bi-gear-fill'],
];
