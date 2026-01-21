const defaultChecklist = [
    { key: 'exit_interview', label: 'Exit Interview', done: false },
    { key: 'asset_return', label: 'Pengembalian Aset (Laptop, HP)', done: false },
    { key: 'access_card_return', label: 'Pengembalian Kartu Akses', done: false },
    { key: 'handover', label: 'Serah Terima Pekerjaan', done: false },
    { key: 'final_pay', label: 'Perhitungan Gaji Terakhir', done: false },
];

async function initOffboardingPage() {
    loadOffboardingData();

    const form = document.getElementById('checklist-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('offboarding_id').value;
            const checklistData = {};
            document.querySelectorAll('#checklist-items-container input[type="checkbox"]').forEach(chk => {
                checklistData[chk.name] = chk.checked;
            });

            const formData = new FormData();
            formData.append('action', 'update_checklist');
            formData.append('id', id);
            formData.append('checklist_data', JSON.stringify(checklistData));

            try {
                const response = await fetch(`${basePath}/api/hr/offboarding`, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('checklistModal');
                    loadOffboardingData();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }

    const btnFinalize = document.getElementById('btn-finalize');
    if (btnFinalize) {
        btnFinalize.addEventListener('click', () => {
            const id = document.getElementById('offboarding_id').value;
            finalizeOffboarding(id);
        });
    }
}

async function loadOffboardingData() {
    const tbody = document.getElementById('offboarding-table-body');
    if (!tbody) return;
    
    try {
        const response = await fetch(`${basePath}/api/hr/offboarding?action=list`);
        const result = await response.json();
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                const statusClass = item.status === 'selesai' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800';
                return `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">${item.nama_lengkap}</div>
                        <div class="text-xs text-gray-500">${item.nip}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.tipe.toUpperCase()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${formatDate(item.tanggal_efektif)}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" title="${item.alasan}">${item.alasan}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${item.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        ${item.status === 'proses' ? `<button onclick='openChecklistModal(${itemJson})' class="text-blue-600 hover:text-blue-900 mr-2">Checklist</button>` : ''}
                        <button onclick="cetakPaklaring(${item.id})" class="text-green-600 hover:text-green-900">Paklaring</button>
                    </td>
                </tr>
            `}).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Tidak ada data offboarding.</td></tr>';
        }
    } catch (error) {
        console.error(error);
    }
}

function openChecklistModal(item) {
    document.getElementById('offboarding_id').value = item.id;
    const container = document.getElementById('checklist-items-container');
    
    let currentChecklist = {};
    try {
        currentChecklist = item.checklist_data ? JSON.parse(item.checklist_data) : {};
    } catch(e) {
        currentChecklist = {};
    }

    container.innerHTML = defaultChecklist.map(chk => {
        const isChecked = currentChecklist[chk.key] === true;
        return `
            <div class="flex items-center">
                <input id="chk-${chk.key}" name="${chk.key}" type="checkbox" ${isChecked ? 'checked' : ''} class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
                <label for="chk-${chk.key}" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">${chk.label}</label>
            </div>
        `;
    }).join('');

    openModal('checklistModal');
}

async function finalizeOffboarding(id) {
    const allChecked = Array.from(document.querySelectorAll('#checklist-items-container input[type="checkbox"]')).every(chk => chk.checked);
    
    if (!allChecked) {
        Swal.fire({
            title: 'Belum Selesai',
            text: "Semua item di checklist harus diselesaikan sebelum finalisasi. Lanjutkan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Lanjutkan Saja'
        }).then((result) => {
            if (result.isConfirmed) {
                processFinalization(id);
            }
        });
    } else {
        if (confirm('Anda yakin ingin memfinalisasi proses offboarding ini? Status karyawan akan menjadi nonaktif.')) {
            processFinalization(id);
        }
    }
}

async function processFinalization(id) {
    const formData = new FormData();
    formData.append('action', 'finalize');
    formData.append('id', id);

    try {
        const response = await fetch(`${basePath}/api/hr/offboarding`, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            Swal.fire('Berhasil!', result.message, 'success');
            closeModal('checklistModal');
            loadOffboardingData();
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        console.error(error);
    }
}

function cetakPaklaring(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank';

    const params = { report: 'paklaring', id: id };
    for (const key in params) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = key;
        hiddenField.value = params[key];
        form.appendChild(hiddenField);
    }
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}