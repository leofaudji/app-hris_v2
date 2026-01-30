function initPortalDokumenPage() {
    // Tab Switching Logic
    const tabs = document.querySelectorAll('[data-tabs-target]');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.getAttribute('data-tabs-target');
            
            // Hide all contents
            document.querySelectorAll('#dokumen-tab-content > div').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show target content
            document.querySelector(targetId).classList.remove('hidden');
            
            // Update tab styles
            tabs.forEach(t => {
                t.classList.remove('text-primary', 'border-primary', 'active-tab-border');
                t.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            tab.classList.add('text-primary', 'border-primary', 'active-tab-border');
            tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        });
    });

    loadDokumenPerusahaan();
    loadRiwayatRequest();

    // Handle Form Submit
    const formRequest = document.getElementById('form-request-surat');
    if (formRequest) {
        formRequest.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(`${basePath}/api/hr/portal/dokumen`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    this.reset();
                    loadRiwayatRequest();
                } else {
                    showToast(data.message || 'Gagal mengirim permintaan', 'error');
                }
            })
            .catch(error => {
                console.error(error);
                showToast('Terjadi kesalahan sistem', 'error');
            });
        });
    }
}

function loadDokumenPerusahaan() {
    const container = document.getElementById('list-dokumen-perusahaan');
    if (!container) return;

    fetch(`${basePath}/api/hr/portal/dokumen?action=list_general_docs`)
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data.length > 0) {
                container.innerHTML = res.data.map(doc => `
                    <div class="flex items-start p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 p-2 bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg mr-3">
                            <i class="bi bi-file-earmark-pdf text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" title="${doc.judul}">
                                ${doc.judul}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${doc.kategori}</p>
                            <a href="${basePath}/${doc.file_path}" target="_blank" class="mt-2 inline-flex items-center text-xs font-medium text-primary hover:underline">
                                <i class="bi bi-download mr-1"></i> Unduh
                            </a>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">Belum ada dokumen tersedia.</div>';
            }
        });
}

function loadRiwayatRequest() {
    const tbody = document.getElementById('list-riwayat-request');
    if (!tbody) return;

    fetch(`${basePath}/api/hr/portal/dokumen?action=list_requests`)
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data.length > 0) {
                tbody.innerHTML = res.data.map(req => {
                    let statusClass = 'bg-gray-100 text-gray-800';
                    if (req.status === 'completed') statusClass = 'bg-green-100 text-green-800';
                    else if (req.status === 'rejected') statusClass = 'bg-red-100 text-red-800';
                    else if (req.status === 'processed') statusClass = 'bg-blue-100 text-blue-800';

                    let aksi = '-';
                    if (req.status === 'completed' && req.file_path) {
                        aksi = `<a href="${basePath}/${req.file_path}" target="_blank" class="text-primary hover:text-primary-600"><i class="bi bi-download"></i> Unduh</a>`;
                    }

                    // Format jenis surat agar lebih rapi
                    const jenisLabel = req.jenis_surat.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                    return `
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${jenisLabel}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${new Date(req.created_at).toLocaleDateString('id-ID')}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                    ${req.status}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                ${aksi}
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-500">Belum ada riwayat permintaan.</td></tr>';
            }
        });
}
