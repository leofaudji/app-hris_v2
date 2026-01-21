async function initKpiTemplatesPage() {
    loadTemplates();

    const form = document.getElementById('template-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Collect indicators
            const rows = document.querySelectorAll('#indicators-container tr');
            const indicators = [];
            let totalBobot = 0;
            rows.forEach(row => {
                const name = row.querySelector('.ind-name').value;
                const weight = parseInt(row.querySelector('.ind-weight').value) || 0;
                if (name) {
                    indicators.push({ indikator: name, bobot: weight });
                    totalBobot += weight;
                }
            });

            if (totalBobot !== 100) {
                Swal.fire('Validasi Gagal', `Total bobot harus 100%. Saat ini: ${totalBobot}%`, 'warning');
                return;
            }

            const formData = new FormData(e.target);
            formData.append('indicators', JSON.stringify(indicators));

            try {
                const response = await fetch(`${basePath}/api/hr/kpi`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('templateModal');
                    loadTemplates();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }
}

async function loadTemplates() {
    const tbody = document.getElementById('template-table-body');
    try {
        const response = await fetch(`${basePath}/api/hr/kpi?action=list_templates`);
        const result = await response.json();
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_template}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">${item.keterangan || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="editTemplate(${item.id}, '${item.nama_template}', '${item.keterangan || ''}')" class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                        <button onclick="deleteTemplate(${item.id})" class="text-red-600 hover:text-red-900">Hapus</button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-gray-500">Belum ada template.</td></tr>';
        }
    } catch (error) {
        console.error(error);
    }
}

function openTemplateModal() {
    document.getElementById('template-form').reset();
    document.getElementById('template_id').value = '';
    document.getElementById('modalTitle').textContent = 'Buat Template KPI';
    document.getElementById('indicators-container').innerHTML = '';
    addIndicatorRow(); // Add one empty row
    updateTotalBobot();
    openModal('templateModal');
}

async function editTemplate(id, nama, ket) {
    document.getElementById('template_id').value = id;
    document.getElementById('nama_template').value = nama;
    document.getElementById('keterangan').value = ket;
    document.getElementById('modalTitle').textContent = 'Edit Template KPI';
    
    // Load indicators
    const response = await fetch(`${basePath}/api/hr/kpi?action=get_template_detail&id=${id}`);
    const result = await response.json();
    const container = document.getElementById('indicators-container');
    container.innerHTML = '';
    
    if (result.success && result.data.length > 0) {
        result.data.forEach(ind => addIndicatorRow(ind.indikator, ind.bobot));
    } else {
        addIndicatorRow();
    }
    updateTotalBobot();
    openModal('templateModal');
}

function addIndicatorRow(name = '', weight = '') {
    const container = document.getElementById('indicators-container');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="p-1"><input type="text" class="ind-name w-full rounded border-gray-300 text-sm" value="${name}" placeholder="Nama Indikator" required></td>
        <td class="p-1"><input type="number" class="ind-weight w-full rounded border-gray-300 text-sm text-center" value="${weight}" placeholder="0" min="0" max="100" oninput="updateTotalBobot()" required></td>
        <td class="p-1 text-center"><button type="button" onclick="this.closest('tr').remove(); updateTotalBobot();" class="text-red-500"><i class="bi bi-x-lg"></i></button></td>
    `;
    container.appendChild(tr);
}

function updateTotalBobot() {
    const inputs = document.querySelectorAll('.ind-weight');
    let total = 0;
    inputs.forEach(inp => total += (parseInt(inp.value) || 0));
    const el = document.getElementById('total-bobot');
    el.textContent = total + '%';
    el.className = total === 100 ? 'text-center font-bold text-green-600' : 'text-center font-bold text-red-600';
}

async function deleteTemplate(id) {
    if (!confirm('Hapus template ini?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_template');
    formData.append('id', id);
    await fetch(`${basePath}/api/hr/kpi`, { method: 'POST', body: formData });
    loadTemplates();
}

// Expose functions globally
window.updateTotalBobot = updateTotalBobot;