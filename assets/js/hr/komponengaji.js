function initKomponenGajiPage() {
    if (document.getElementById('komponen-table-body')) {
        loadKomponen();

        const saveBtn = document.getElementById('save-komponen-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveKomponen);
    }
}

function loadKomponen() {
    const tbody = document.getElementById('komponen-table-body');

    fetch(`${basePath}/api/hr/komponen-gaji`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada komponen gaji yang dibuat.</td></tr>';
                    return;
                }
                
                const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

                data.data.forEach(item => {
                    const jenisBadge = item.jenis === 'pendapatan'
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Pendapatan</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Potongan</span>';
                    
                    const tipeBadge = item.tipe_hitung === 'harian'
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Harian</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Bulanan</span>';

                    const defaultBadge = item.is_default == 1
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Ya</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Tidak</span>';

                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_komponen}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${jenisBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${tipeBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">${formatter.format(item.nilai_default)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${defaultBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editKomponen(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteKomponen(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
        });
}

function openKomponenModal(reset = true) {
    if (reset) {
        document.getElementById('komponen-form').reset();
        document.getElementById('komponen-id').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Komponen Gaji';
    }
    document.getElementById('komponenModal').classList.remove('hidden');
}

function closeKomponenModal() {
    document.getElementById('komponenModal').classList.add('hidden');
}

function editKomponen(item) {
    openKomponenModal(false);
    document.getElementById('komponen-id').value = item.id;
    document.getElementById('modal-title').innerText = 'Edit Komponen Gaji';
    document.getElementById('nama_komponen').value = item.nama_komponen;
    document.getElementById('jenis').value = item.jenis;
    document.getElementById('tipe_hitung').value = item.tipe_hitung || 'bulanan';
    document.getElementById('nilai_default').value = parseFloat(item.nilai_default);
    document.getElementById('is_default').value = item.is_default;
}

function saveKomponen() {
    const form = document.getElementById('komponen-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/komponen-gaji`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeKomponenModal();
            loadKomponen();
            showToast('Data komponen berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteKomponen(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus komponen ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/komponen-gaji`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadKomponen();
            showToast('Data komponen berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}