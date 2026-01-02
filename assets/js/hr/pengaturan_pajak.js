function initPengaturanPajakPage() {
    if (document.getElementById('pajak-settings-form')) {
        loadPajakSettings();
        document.getElementById('save-pajak-btn').addEventListener('click', savePajakSettings);
    }
}

function loadPajakSettings() {
    fetch(`${basePath}/api/hr/pengaturan-pajak`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                for (const key in data) {
                    const input = document.getElementById(key);
                    if (input) {
                        input.value = data[key];
                    }
                }
            } else {
                showToast(res.message || 'Gagal memuat pengaturan.', 'error');
            }
        });
}

function savePajakSettings() {
    const form = document.getElementById('pajak-settings-form');
    const formData = new FormData(form);

    fetch(`${basePath}/api/hr/pengaturan-pajak`, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Gagal menyimpan pengaturan', 'error');
            }
        });
}