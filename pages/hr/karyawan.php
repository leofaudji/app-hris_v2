<?php
$is_spa_request = isset($_SERVER['HTTP_X_SPA_REQUEST']) && $_SERVER['HTTP_X_SPA_REQUEST'] === 'true';
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/header.php';
}

// Security check (Pastikan permission 'hr_karyawan' sudah ditambahkan ke database jika ingin mengaktifkan ini)
// check_permission('hr_karyawan', 'view');
?>

<div class="flex justify-between flex-wrap items-center pt-3 pb-2 mb-3 border-b border-gray-200 dark:border-gray-700">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
        <i class="bi bi-people-fill"></i> Manajemen Karyawan
    </h1>
    <div class="flex mb-2 md:mb-0">
        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" onclick="openKaryawanModal()">
            <i class="bi bi-plus-circle mr-2"></i> Tambah Karyawan
        </button>
    </div>
</div>

<!-- Filter & Search -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search-karyawan" class="sr-only">Cari</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-search text-gray-400"></i>
                    </div>
                    <input type="text" id="search-karyawan" class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" placeholder="Cari NIP atau Nama...">
                </div>
            </div>
            <div>
                <select id="filter-status" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">NIP</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama Lengkap</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Divisi</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kantor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gol. Gaji</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jadwal Kerja</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jabatan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal Masuk</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="karyawan-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Data akan dimuat di sini -->
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Karyawan -->
<div id="karyawanModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeKaryawanModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Form Karyawan</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeKaryawanModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="karyawan-form">
                    <input type="hidden" name="id" id="karyawan-id">
                    <input type="hidden" name="action" id="karyawan-action" value="save">
                    
                    <div class="mb-4">
                        <label for="nip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP</label>
                        <input type="text" name="nip" id="nip" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>

                    <div class="mb-4">
                        <label for="divisi_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Divisi</label>
                        <select name="divisi_id" id="divisi_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Pilih Divisi</option>
                            <!-- Opsi divisi dimuat via JS -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="kantor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kantor</label>
                        <select name="kantor_id" id="kantor_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Pilih Kantor</option>
                            <!-- Opsi kantor dimuat via JS -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="golongan_gaji_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Golongan Gaji</label>
                        <select name="golongan_gaji_id" id="golongan_gaji_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Pilih Golongan Gaji</option>
                            <!-- Opsi golongan gaji dimuat via JS -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="jadwal_kerja_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jadwal Kerja</label>
                        <select name="jadwal_kerja_id" id="jadwal_kerja_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Pilih Jadwal</option>
                            <!-- Opsi jadwal dimuat via JS -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="jabatan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                        <select name="jabatan_id" id="jabatan_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="">Pilih Jabatan</option>
                            <!-- Opsi jabatan dimuat via JS -->
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="tanggal_masuk" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" id="tanggal_masuk" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Informasi Pajak & BPJS</h4>
                        
                        <div class="mb-4">
                            <label for="npwp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NPWP</label>
                            <input type="text" name="npwp" id="npwp" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>

                        <div class="mb-4">
                            <label for="status_ptkp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status PTKP</label>
                            <select name="status_ptkp" id="status_ptkp" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="TK/0">TK/0 - Tidak Kawin, 0 Tanggungan</option>
                                <option value="K/0">K/0 - Kawin, 0 Tanggungan</option>
                                <option value="K/1">K/1 - Kawin, 1 Tanggungan</option>
                                <option value="K/2">K/2 - Kawin, 2 Tanggungan</option>
                                <option value="K/3">K/3 - Kawin, 3 Tanggungan</option>
                            </select>
                        </div>

                        <div class="flex items-center mb-2">
                            <input type="checkbox" name="ikut_bpjs_kes" id="ikut_bpjs_kes" value="1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="ikut_bpjs_kes" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Ikut BPJS Kesehatan</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="ikut_bpjs_tk" id="ikut_bpjs_tk" value="1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="ikut_bpjs_tk" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Ikut BPJS Ketenagakerjaan</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="save-karyawan-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                <button type="button" onclick="closeKaryawanModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
            </div>
        </div>
    </div>
</div>

<?php
if (!$is_spa_request) {
    require_once PROJECT_ROOT . '/views/footer.php';
}
?>