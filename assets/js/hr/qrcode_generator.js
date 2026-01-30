let qrRefreshInterval;

function initQrCodeGeneratorPage() {
    loadKantorOptions();
}

async function loadKantorOptions() {
    const select = document.getElementById('qr-kantor-select');
    if (!select) return;

    try {
        const response = await fetch(`${basePath}/api/hr/kantor`);
        const result = await response.json();

        if (result.success) {
            select.innerHTML = '<option value="">-- Pilih Kantor --</option>';
            result.data.forEach(kantor => {
                const option = document.createElement('option');
                option.value = kantor.id;
                option.textContent = kantor.nama_kantor;
                option.dataset.nama = kantor.nama_kantor; // Simpan nama untuk referensi
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Gagal memuat data kantor:', error);
        showToast('Gagal memuat data kantor.', 'error');
    }
}

function generateQrCode() {
    const select = document.getElementById('qr-kantor-select');
    const lokasiInput = document.getElementById('qr-lokasi-input');
    const layoutSelect = document.getElementById('qr-layout-select');
    const kantorId = select.value;
    
    if (!kantorId) {
        showToast('Silakan pilih kantor terlebih dahulu.', 'error');
        return;
    }

    const namaKantor = select.options[select.selectedIndex].dataset.nama;
    const lokasiLabel = lokasiInput.value.trim() || namaKantor;
    const layout = layoutSelect ? layoutSelect.value : 'modern';

    // Construct URL for raw image
    const qrUrlRaw = `${basePath}/qr/img/${kantorId}/${encodeURIComponent(lokasiLabel)}`;
    // Construct URL for public view page
    const qrUrlPublic = `${basePath}/qr/absensi/${kantorId}/${encodeURIComponent(lokasiLabel)}?layout=${layout}`;

    // Update UI
    const qrImage = document.getElementById('qr-image');
    qrImage.src = qrUrlRaw;

    // Auto refresh preview setiap 30 detik agar tidak kadaluarsa saat ditampilkan
    if (qrRefreshInterval) clearInterval(qrRefreshInterval);
    qrRefreshInterval = setInterval(() => {
        qrImage.src = `${qrUrlRaw}&t=${new Date().getTime()}`;
    }, 30000);

    document.getElementById('preview-title').textContent = lokasiLabel;
    document.getElementById('public-link').href = qrUrlPublic;

    document.getElementById('qr-placeholder').classList.add('hidden');
    document.getElementById('qr-preview-container').classList.remove('hidden');
}

function printQrCode() {
    const printContent = document.getElementById('qr-preview-container').innerHTML;
    const originalContent = document.body.innerHTML;

    // Simple print trick: replace body, print, restore
    // Note: A better way is using a print-specific CSS class or a separate window
    const win = window.open('', '', 'height=700,width=700');
    win.document.write('<html><head><title>Print QR Code</title>');
    win.document.write('<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">'); // Optional styling
    win.document.write('</head><body class="flex items-center justify-center h-screen">');
    win.document.write(printContent);
    win.document.write('</body></html>');
    win.document.close();
    
    // Wait for image to load in new window before printing
    win.onload = function() {
        // Hide buttons in print view
        const buttons = win.document.querySelectorAll('button, a');
        buttons.forEach(btn => btn.style.display = 'none');
        
        win.focus();
        win.print();
        win.close();
    };
}