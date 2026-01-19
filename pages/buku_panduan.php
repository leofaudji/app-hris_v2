<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}
?>

<div class="flex justify-between flex-wrap md:flex-nowrap items-center pt-3 pb-2 mb-6 border-b border-gray-200 dark:border-gray-700">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <span class="p-2 bg-blue-100 text-blue-600 rounded-lg dark:bg-blue-900/30 dark:text-blue-400">
                <i class="bi bi-book-half"></i>
            </span>
            Buku Panduan Aplikasi
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Dokumentasi lengkap fitur Keuangan, HRIS, dan Inventaris.</p>
    </div>
    <div class="flex mt-4 md:mt-0">
        <a href="<?= base_url('/api/pdf?report=buku-panduan') ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
            <i class="bi bi-file-earmark-pdf-fill text-red-500 mr-2"></i> Cetak PDF
        </a>
    </div>
</div>

<div class="space-y-6" id="panduanAccordion">

    <!-- 1. OVERVIEW -->
    <div class="border border-indigo-100 dark:border-indigo-900/50 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingOverview">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-900/20 dark:to-gray-800 hover:from-indigo-100 dark:hover:from-indigo-900/30 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-diagram-3-fill text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Alur Kerja Sistem (Workflow)</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Gambaran umum integrasi modul Keuangan, HR, dan Stok.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-indigo-500"></i>
            </button>
        </h2>
        <div id="collapseOverview" class="block p-6 border-t border-indigo-100 dark:border-indigo-900/50 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800" aria-labelledby="headingOverview">
            <p class="mb-6 text-center max-w-3xl mx-auto">Aplikasi ini mengintegrasikan tiga pilar utama operasional bisnis: <strong>Keuangan</strong>, <strong>Sumber Daya Manusia (HR)</strong>, dan <strong>Inventaris</strong>. Memahami alur ini membantu Anda memaksimalkan efisiensi dan akurasi data.</p>
            <div class="mermaid-container overflow-x-auto p-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                <pre class="mermaid text-center">
                graph TD
                    classDef finance fill:#e0f2fe,stroke:#1e40af,stroke-width:2px,color:#1e3a8a
                    classDef hr fill:#fce7f3,stroke:#9d174d,stroke-width:2px,color:#831843
                    classDef stock fill:#dcfce7,stroke:#166534,stroke-width:2px,color:#14532d
                    classDef admin fill:#f3f4f6,stroke:#4b5563,stroke-width:2px,color:#374151

                    subgraph " "
                        direction TB
                        
                        subgraph "1. Setup & Master Data"
                            A1[Setup Akun & Saldo Awal]:::finance
                            A2[Data Karyawan & Struktur HR]:::hr
                            A3[Master Barang & Aset]:::stock
                        end

                        subgraph "2. Operasional Harian"
                            B1(Transaksi Kas & Jurnal):::finance
                            B2(Absensi & Pengajuan Cuti):::hr
                            B3(Pembelian & Penjualan):::stock
                        end

                        subgraph "3. Proses Periodik"
                            C1(Rekonsiliasi Bank):::finance
                            C2(Proses Penggajian):::hr
                            C3(Stok Opname & Penyusutan):::stock
                        end

                        subgraph "4. Laporan & Analisis"
                            D1>Laporan Keuangan]:::finance
                            D2>Slip Gaji & Laporan HR]:::hr
                            D3>Laporan Stok & Aset]:::stock
                        end
                    end

                    A1 --> B1
                    A2 --> B2
                    A3 --> B3

                    B1 --> C1
                    B2 --> C2
                    B3 --> C3

                    C1 --> D1
                    C2 --> D2
                    C3 --> D3
                </pre>
            </div>
        </div>
    </div>

    <!-- 2. MODUL KEUANGAN -->
    <div class="border border-blue-100 dark:border-blue-900/50 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingFinance">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800 hover:from-blue-100 dark:hover:from-blue-900/30 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-wallet2 text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Modul Keuangan & Akuntansi</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Pencatatan transaksi, jurnal otomatis, hingga laporan keuangan.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-blue-500"></i>
            </button>
        </h2>
        <div id="collapseFinance" class="hidden p-6 border-t border-blue-100 dark:border-blue-900/50 text-gray-700 dark:text-gray-300" aria-labelledby="headingFinance">
            <div class="space-y-8">
                <!-- Fitur Utama -->
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-journal-check"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Jurnal Otomatis</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Setiap transaksi kas, pembelian, dan penjualan langsung terjurnal otomatis.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-bank2"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Rekonsiliasi Bank</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Cocokkan catatan sistem dengan rekening koran bank dengan mudah.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-graph-up-arrow"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Laporan Real-time</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Neraca, Laba Rugi, dan Arus Kas tersedia kapan saja secara instan.</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kasus -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                        <h4 class="font-bold text-blue-700 dark:text-blue-400 text-sm flex items-center gap-2">
                            <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Pembayaran Listrik
                        </h4>
                    </div>
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                        <div class="order-2 lg:order-1 space-y-5">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Input Transaksi</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Staf mencatat pengeluaran Rp 300.000 untuk listrik di menu <strong>Transaksi Kas</strong>.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Proses Sistem</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Sistem otomatis menjurnal: <br><span class="font-mono text-xs bg-gray-100 dark:bg-gray-900 px-1 rounded">(Dr) Beban Listrik / (Cr) Kas</span>.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300 flex items-center justify-center font-bold text-sm">3</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Hasil Akhir</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Laporan Laba Rugi langsung mencatat beban, dan saldo Kas di Neraca berkurang.</p>
                                </div>
                            </div>
                        </div>
                        <div class="order-1 lg:order-2 flex justify-center bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <img src="https://raw.githubusercontent.com/CreatCodeBuild/asset/main/finance-flowchart.svg" alt="Financial Workflow" class="max-h-48 w-auto drop-shadow-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MODUL HRIS - KEPEGAWAIAN -->
    <div class="border border-rose-100 dark:border-rose-900/50 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingHR">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-rose-50 to-white dark:from-rose-900/20 dark:to-gray-800 hover:from-rose-100 dark:hover:from-rose-900/30 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-people-fill text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Modul HRIS: Kepegawaian</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Pusat data karyawan, struktur organisasi, kehadiran, dan cuti.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-rose-500"></i>
            </button>
        </h2>
        <div id="collapseHR" class="hidden p-6 border-t border-rose-100 dark:border-rose-900/50 text-gray-700 dark:text-gray-300" aria-labelledby="headingHR">
            <div class="space-y-8">
                <!-- Fitur Utama -->
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-800 dark:text-rose-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-person-vcard"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Database Lengkap</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Simpan data pribadi, kontrak, NPWP, hingga BPJS karyawan dalam satu tempat.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-800 dark:text-rose-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-diagram-3"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Struktur Organisasi</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Atur hierarki perusahaan dengan manajemen Divisi, Jabatan, dan Kantor.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-800 dark:text-rose-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-alarm"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Monitoring Kontrak</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Dashboard otomatis menampilkan peringatan untuk kontrak yang akan habis.</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kasus -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                        <h4 class="font-bold text-rose-700 dark:text-rose-400 text-sm flex items-center gap-2">
                            <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Karyawan Baru
                        </h4>
                    </div>
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                        <div class="order-2 lg:order-1 space-y-5">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900 dark:text-rose-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Input Data</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">HRD memasukkan data 'Citra' ke menu <strong>Karyawan</strong>, mengatur Jabatan 'Staff' dan Gaji Pokok.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-pink-100 text-pink-600 dark:bg-pink-900 dark:text-pink-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Integrasi</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Data Citra otomatis tersedia di modul Absensi dan Payroll. Akun Portal Karyawan juga dibuat.</p>
                                </div>
                            </div>
                        </div>
                        <div class="order-1 lg:order-2 flex justify-center bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <img src="https://raw.githubusercontent.com/CreatCodeBuild/asset/main/hris-flowchart.svg" alt="HRIS Workflow" class="max-h-48 w-auto drop-shadow-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MODUL HRIS - ABSENSI & CUTI -->
    <div class="border border-purple-100 dark:border-purple-900/50 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingAbsensi">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800 hover:from-purple-100 dark:hover:from-purple-900/30 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-calendar-check-fill text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Modul HRIS: Absensi & Cuti</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Pencatatan kehadiran dan manajemen permohonan cuti.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-purple-500"></i>
            </button>
        </h2>
        <div id="collapseAbsensi" class="hidden p-6 border-t border-purple-100 dark:border-purple-900/50 text-gray-700 dark:text-gray-300" aria-labelledby="headingAbsensi">
            <div class="space-y-8">
                <!-- Fitur Utama -->
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-clock-history"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Tracking Kehadiran</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Catat jam masuk/pulang, hitung keterlambatan, dan rekap kehadiran bulanan.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-calendar-heart"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Manajemen Cuti</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pengajuan cuti online, persetujuan berjenjang, dan pemotongan kuota otomatis.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-800 dark:text-purple-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-calendar3"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Kalender Visual</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Lihat siapa yang cuti atau hadir dalam tampilan kalender yang intuitif.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Info Tambahan -->
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-100 dark:border-purple-900/50 flex items-start gap-3">
                    <i class="bi bi-info-circle-fill text-purple-600 mt-1"></i>
                    <div>
                        <h5 class="font-bold text-purple-700 dark:text-purple-300 text-sm">Integrasi Payroll</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Data absensi (jumlah kehadiran, keterlambatan) akan ditarik secara otomatis saat proses penggajian untuk menghitung tunjangan harian atau potongan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. MODUL HRIS - PAYROLL -->
    <div class="border border-amber-100 dark:border-amber-900/50 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingPayroll">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-amber-50 to-white dark:from-amber-900/20 dark:to-gray-800 hover:from-amber-100 dark:hover:from-amber-900/30 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-cash-coin text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Modul HRIS: Payroll (Penggajian)</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Perhitungan gaji, tunjangan, potongan, dan PPh 21.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-amber-500"></i>
            </button>
        </h2>
        <div id="collapsePayroll" class="hidden p-6 border-t border-amber-100 dark:border-amber-900/50 text-gray-700 dark:text-gray-300" aria-labelledby="headingPayroll">
            <div class="space-y-8">
                <!-- Fitur Utama -->
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-sliders"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Komponen Fleksibel</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Atur tunjangan tetap, harian, atau potongan sesuai kebijakan perusahaan.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-calculator"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Pajak & BPJS</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Perhitungan otomatis PPh 21 (TER) dan iuran BPJS Ketenagakerjaan/Kesehatan.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-receipt"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Slip Gaji Digital</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Generate slip gaji massal dan distribusikan langsung ke portal karyawan.</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kasus -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                        <h4 class="font-bold text-amber-700 dark:text-amber-400 text-sm flex items-center gap-2">
                            <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Penggajian Akhir Bulan
                        </h4>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex gap-4 items-start">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900 dark:text-amber-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Generate</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">HRD klik "Generate Gaji". Sistem menarik Gaji Pokok, Tunjangan Jabatan, dan menghitung Tunjangan Makan berdasarkan jumlah kehadiran dari modul Absensi.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 items-start">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Kalkulasi Otomatis</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Sistem menghitung potongan BPJS dan PPh 21 sesuai status PTKP karyawan.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 items-start">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300 flex items-center justify-center font-bold text-sm">3</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Finalisasi</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Setelah review, HRD memfinalisasi gaji. Jurnal beban gaji terbentuk otomatis, dan slip gaji muncul di portal karyawan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. MODUL STOK & ASET -->
    <div class="border border-emerald-100 dark:border-emerald-900/50 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingStock">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-emerald-50 to-white dark:from-emerald-900/20 dark:to-gray-800 hover:from-emerald-100 dark:hover:from-emerald-900/30 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-box-seam-fill text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Modul Stok & Aset Tetap</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Manajemen persediaan barang dan penyusutan aset.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-emerald-500"></i>
            </button>
        </h2>
        <div id="collapseStock" class="hidden p-6 border-t border-emerald-100 dark:border-emerald-900/50 text-gray-700 dark:text-gray-300" aria-labelledby="headingStock">
            <div class="space-y-8">
                <!-- Fitur Utama -->
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-800 dark:text-emerald-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-box-seam"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Stok Real-time</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Stok otomatis bertambah saat pembelian dan berkurang saat penjualan.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-800 dark:text-emerald-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-card-list"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Kartu Stok</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Audit trail lengkap untuk setiap pergerakan barang masuk dan keluar.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-800 dark:text-emerald-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-building-gear"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Aset & Depresiasi</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Manajemen aset tetap dengan perhitungan penyusutan otomatis.</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kasus -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                        <h4 class="font-bold text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-2">
                            <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Stok Opname
                        </h4>
                    </div>
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                        <div class="order-2 lg:order-1 space-y-5">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300 flex items-center justify-center font-bold text-sm">1</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Temuan Fisik</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Admin gudang menemukan selisih stok 'Buku Tulis' (Sistem: 100, Fisik: 98).</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 dark:bg-teal-900 dark:text-teal-300 flex items-center justify-center font-bold text-sm">2</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Penyesuaian</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Admin input stok fisik 98 di menu <strong>Stok Opname</strong>.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 mt-1"><span class="w-8 h-8 rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300 flex items-center justify-center font-bold text-sm">3</span></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Otomatisasi</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Sistem mengupdate stok, mencatat di Kartu Stok, dan membuat jurnal penyesuaian persediaan (kerugian).</p>
                                </div>
                            </div>
                        </div>
                        <div class="order-1 lg:order-2 flex justify-center bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                            <img src="https://raw.githubusercontent.com/CreatCodeBuild/asset/main/inventory-flowchart.svg" alt="Inventory Workflow" class="max-h-48 w-auto drop-shadow-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. PORTAL KARYAWAN -->
    <div class="border border-cyan-100 dark:border-cyan-900/50 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingPortal">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-cyan-50 to-white dark:from-cyan-900/20 dark:to-gray-800 hover:from-cyan-100 dark:hover:from-cyan-900/30 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-900/50 dark:text-cyan-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-person-workspace text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Portal Karyawan</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Fitur self-service untuk setiap karyawan.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-cyan-500"></i>
            </button>
        </h2>
        <div id="collapsePortal" class="hidden p-6 border-t border-cyan-100 dark:border-cyan-900/50 text-gray-700 dark:text-gray-300" aria-labelledby="headingPortal">
            <div class="space-y-8">
                <!-- Fitur Utama -->
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-800 dark:text-cyan-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-phone"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Akses Mandiri</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Karyawan dapat login untuk melihat data pribadi dan mengajukan cuti.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-800 dark:text-cyan-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-eye"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Transparansi</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Lihat riwayat kehadiran dan sisa kuota cuti secara real-time.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-100 dark:border-cyan-800 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-800 dark:text-cyan-200 flex items-center justify-center mb-3 text-lg"><i class="bi bi-download"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Unduh Dokumen</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Download slip gaji bulanan dalam format PDF kapan saja.</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kasus -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                        <h4 class="font-bold text-cyan-700 dark:text-cyan-400 text-sm flex items-center gap-2">
                            <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Cek Slip Gaji
                        </h4>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                            Karyawan bernama 'Budi' ingin memeriksa rincian gajinya bulan lalu untuk keperluan pengajuan kredit.
                        </p>
                        <div class="flex items-center gap-4 p-4 bg-cyan-50 dark:bg-cyan-900/20 rounded-lg border border-cyan-100 dark:border-cyan-800">
                            <i class="bi bi-phone-vibrate text-2xl text-cyan-600"></i>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Budi login ke aplikasi -> Buka menu <strong>Slip Gaji</strong> -> Pilih Periode -> Klik <strong>Download PDF</strong>. <br>
                                <span class="text-xs text-gray-500 dark:text-gray-400 italic">Tanpa perlu menghubungi HRD secara manual.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. ADMINISTRASI -->
    <div class="border border-slate-100 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all hover:shadow-md" data-controller="accordion-item">
        <h2 class="mb-0" id="headingAdmin">
            <button class="w-full flex items-center justify-between px-6 py-4 text-left bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-gray-800 hover:from-slate-100 dark:hover:from-slate-700 transition-all duration-300 group" type="button" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <i class="bi bi-gear-fill text-xl"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold text-gray-800 dark:text-white">Administrasi & Pengaturan</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Manajemen user, hak akses, dan konfigurasi sistem.</span>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-gray-400 transform transition-transform duration-300 group-hover:text-slate-500"></i>
            </button>
        </h2>
        <div id="collapseAdmin" class="hidden p-6 border-t border-slate-100 dark:border-slate-700 text-gray-700 dark:text-gray-300" aria-labelledby="headingAdmin">
            <div class="space-y-8">
                <!-- Fitur Utama -->
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="bi bi-stars text-yellow-500"></i> Fitur Unggulan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 flex items-center justify-center mb-3 text-lg"><i class="bi bi-shield-lock"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Role Management</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Batasi akses menu berdasarkan peran (misal: Kasir, HRD, Manager).</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 flex items-center justify-center mb-3 text-lg"><i class="bi bi-activity"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Audit Log</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pantau setiap aktivitas login dan perubahan data oleh pengguna.</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 flex items-center justify-center mb-3 text-lg"><i class="bi bi-archive"></i></div>
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-1">Tutup Buku</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Proses akhir tahun untuk memindahkan laba rugi ke modal secara otomatis.</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kasus -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 px-4">
                        <h4 class="font-bold text-slate-700 dark:text-slate-400 text-sm flex items-center gap-2">
                            <i class="bi bi-lightbulb-fill"></i> Studi Kasus: Pembatasan Akses
                        </h4>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                            Perusahaan ingin agar <strong>Staf Akuntansi</strong> hanya fokus pada keuangan dan tidak bisa melihat data gaji karyawan lain.
                        </p>
                        <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
                            <i class="bi bi-person-lock text-2xl text-slate-600"></i>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Admin membuat role 'Accounting' -> Centang menu 'Transaksi', 'Akuntansi', 'Laporan' -> Hapus centang menu 'HR & Payroll'. <br>
                                <span class="text-xs text-gray-500 dark:text-gray-400 italic">Hasil: Staf Akuntansi aman bekerja tanpa risiko kebocoran data HR.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Mermaid.js untuk merender diagram -->
<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ securityLevel: 'loose', theme: document.documentElement.classList.contains('dark') ? 'dark' : 'neutral' });
</script>

<script>
    function toggleAccordion(button) {
        const item = button.closest('[data-controller="accordion-item"]');
        const content = item.querySelector('div[id^="collapse"]');
        const icon = button.querySelector('.bi-chevron-down');

        if (content.classList.contains('hidden')) {
            // Close all other accordions first
            document.querySelectorAll('#panduanAccordion [data-controller="accordion-item"]').forEach(otherItem => {
                const otherContent = otherItem.querySelector('div[id^="collapse"]');
                const otherIcon = otherItem.querySelector('.bi-chevron-down');
                if (otherItem !== item && !otherContent.classList.contains('hidden')) {
                    otherContent.classList.add('hidden');
                    otherIcon.classList.remove('rotate-180');
                }
            });

            // Open the clicked one
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            // Close the clicked one
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }
</script>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>
