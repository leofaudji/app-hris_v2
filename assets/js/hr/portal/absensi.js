let currentAction = '';
let userLocation = '';
let html5QrCode;
let stream;

function initPortalAbsensiPage() {
    const dateDisplay = document.getElementById('current-date-display');
    if(dateDisplay) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateDisplay.textContent = new Date().toLocaleDateString('id-ID', options);
    }

    loadTodayStatus();
    loadHistory();
    getLocation();

    // Event Listeners
    document.getElementById('btn-clock-in')?.addEventListener('click', () => openAbsenModal('clock_in'));
    document.getElementById('btn-clock-out')?.addEventListener('click', () => openAbsenModal('clock_out'));
    document.getElementById('btn-close-modal')?.addEventListener('click', closeAbsenModal);
    document.getElementById('absenModalOverlay')?.addEventListener('click', closeAbsenModal);
    
    document.getElementById('tab-camera')?.addEventListener('click', () => switchTab('camera'));
    document.getElementById('tab-qr')?.addEventListener('click', () => switchTab('qr'));
    document.getElementById('btn-take-snapshot')?.addEventListener('click', takeSnapshot);
}

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userLocation = `${position.coords.latitude},${position.coords.longitude}`;
                const locEl = document.getElementById('location-info');
                if(locEl) locEl.innerHTML = `<i class="bi bi-geo-alt-fill text-green-500"></i> Lokasi terkunci: ${userLocation}`;
            },
            (error) => {
                console.error("Error getting location:", error);
                const locEl = document.getElementById('location-info');
                if(locEl) locEl.innerHTML = `<i class="bi bi-exclamation-triangle text-red-500"></i> Gagal mengambil lokasi. Pastikan GPS aktif.`;
            }
        );
    }
}

async function loadTodayStatus() {
    try {
        const response = await fetch(`${basePath}/api/hr/portal/absensi?action=today_status`);
        const result = await response.json();
        
        const btnIn = document.getElementById('btn-clock-in');
        const btnOut = document.getElementById('btn-clock-out');
        const statusCompleted = document.getElementById('status-completed');
        const dispMasuk = document.getElementById('display-jam-masuk');
        const dispKeluar = document.getElementById('display-jam-keluar');

        if (result.success && result.data) {
            const data = result.data;
            dispMasuk.textContent = data.jam_masuk ? data.jam_masuk.substring(0, 5) : '--:--';
            dispKeluar.textContent = data.jam_keluar ? data.jam_keluar.substring(0, 5) : '--:--';

            if (data.jam_masuk && !data.jam_keluar) {
                // Sudah masuk, belum pulang
                btnIn.classList.add('hidden');
                btnOut.classList.remove('hidden');
                statusCompleted.classList.add('hidden');
            } else if (data.jam_masuk && data.jam_keluar) {
                // Sudah selesai
                btnIn.classList.add('hidden');
                btnOut.classList.add('hidden');
                statusCompleted.classList.remove('hidden');
            }
        } else {
            // Belum absen sama sekali
            btnIn.classList.remove('hidden');
            btnOut.classList.add('hidden');
            statusCompleted.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error loading status:', error);
    }
}

async function loadHistory() {
    const tbody = document.getElementById('absensi-history-body');
    const mobileList = document.getElementById('absensi-mobile-list');
    
    const bulan = document.getElementById('filter-bulan')?.value || new Date().getMonth() + 1;
    const tahun = document.getElementById('filter-tahun')?.value || new Date().getFullYear();
    
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Memuat...</td></tr>';
    if (mobileList) mobileList.innerHTML = '<div class="text-center py-4 text-gray-500">Memuat data...</div>';
    
    try {
        const response = await fetch(`${basePath}/api/hr/portal/absensi?bulan=${bulan}&tahun=${tahun}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            if (tbody) tbody.innerHTML = '';
            if (mobileList) mobileList.innerHTML = '';

            result.data.forEach(row => {
                const badgeClass = row.badge_class || 'bg-gray-100 text-gray-800';
                
                // Badge Jenis Absensi
                let jenisBadge = '<span class="text-xs text-gray-400">-</span>';
                if (row.jenis_absensi === 'qrcode') {
                    jenisBadge = '<span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200 border border-purple-200 dark:border-purple-800"><i class="bi bi-qr-code"></i> QR</span>';
                } else if (row.jenis_absensi === 'selfie') {
                    jenisBadge = '<span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 border border-blue-200 dark:border-blue-800"><i class="bi bi-camera"></i> Selfie</span>';
                }

                // Tombol Foto
                let photoBtn = '-';
                if (row.foto_masuk) {
                    photoBtn = `<button onclick="viewPhoto('${basePath}/${row.foto_masuk}', 'Foto Masuk')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400"><i class="bi bi-image"></i> Lihat</button>`;
                }

                // Render Tabel Desktop
                if (tbody) {
                    const tr = `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${new Date(row.tanggal).toLocaleDateString('id-ID')}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${row.jam_masuk ? row.jam_masuk.substring(0,5) : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${row.jam_keluar ? row.jam_keluar.substring(0,5) : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badgeClass}">${row.status}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${jenisBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">${photoBtn}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${row.keterangan || '-'}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', tr);
                }

                // Render Kartu Mobile
                if (mobileList) {
                    const card = `
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">${new Date(row.tanggal).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">${row.keterangan || '-'}</div>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${badgeClass}">${row.status}</span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded border border-gray-100 dark:border-gray-600 text-center">
                                    <div class="text-[10px] text-gray-500 uppercase tracking-wider">Masuk</div>
                                    <div class="font-mono font-bold text-green-600 dark:text-green-400 text-lg">${row.jam_masuk ? row.jam_masuk.substring(0,5) : '--:--'}</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-2 rounded border border-gray-100 dark:border-gray-600 text-center">
                                    <div class="text-[10px] text-gray-500 uppercase tracking-wider">Pulang</div>
                                    <div class="font-mono font-bold text-red-600 dark:text-red-400 text-lg">${row.jam_keluar ? row.jam_keluar.substring(0,5) : '--:--'}</div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-3 border-t border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-2">
                                    ${jenisBadge}
                                </div>
                                <div class="text-sm">
                                    ${photoBtn}
                                </div>
                            </div>
                        </div>
                    `;
                    mobileList.insertAdjacentHTML('beforeend', card);
                }
            });
        } else {
            const emptyMsg = 'Belum ada riwayat absensi.';
            if (tbody) tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-gray-500">${emptyMsg}</td></tr>`;
            if (mobileList) mobileList.innerHTML = `<div class="text-center py-8 text-gray-500">${emptyMsg}</div>`;
        }
    } catch (error) {
        const errorMsg = 'Gagal memuat data.';
        if (tbody) tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-red-500">${errorMsg}</td></tr>`;
        if (mobileList) mobileList.innerHTML = `<div class="text-center py-8 text-red-500">${errorMsg}</div>`;
    }
}

function openAbsenModal(action) {
    currentAction = action;
    document.getElementById('modal-absen-title').textContent = action === 'clock_in' ? 'Absen Masuk' : 'Absen Pulang';
    document.getElementById('absenModal').classList.remove('hidden');
    
    // Default open camera tab
    switchTab('camera');
}

function closeAbsenModal() {
    document.getElementById('absenModal').classList.add('hidden');
    stopCamera();
    stopQrScanner();
}

function switchTab(tab) {
    const tabCamera = document.getElementById('tab-camera');
    const tabQr = document.getElementById('tab-qr');
    const secCamera = document.getElementById('section-camera');
    const secQr = document.getElementById('section-qr');

    if (tab === 'camera') {
        tabCamera.classList.add('text-primary', 'border-b-2', 'border-primary');
        tabCamera.classList.remove('text-gray-500');
        tabQr.classList.remove('text-primary', 'border-b-2', 'border-primary');
        tabQr.classList.add('text-gray-500');
        
        secCamera.classList.remove('hidden');
        secQr.classList.add('hidden');
        
        stopQrScanner();
        startCamera();
    } else {
        tabQr.classList.add('text-primary', 'border-b-2', 'border-primary');
        tabQr.classList.remove('text-gray-500');
        tabCamera.classList.remove('text-primary', 'border-b-2', 'border-primary');
        tabCamera.classList.add('text-gray-500');

        secQr.classList.remove('hidden');
        secCamera.classList.add('hidden');

        stopCamera();
        startQrScanner();
    }
}

// --- Camera Logic ---
async function startCamera() {
    const video = document.getElementById('webcam');
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Browser tidak mendukung akses kamera atau koneksi tidak aman (HTTP). Harap gunakan HTTPS.");
        return;
    }

    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
        video.srcObject = stream;
    } catch (err) {
        console.error("Error accessing webcam", err);
        let msg = "Gagal mengakses kamera. Pastikan izin diberikan.";
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            msg = "Akses kamera diblokir karena koneksi tidak aman (HTTP). Harap akses aplikasi menggunakan HTTPS atau localhost.";
        } else if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            msg = "Izin kamera ditolak. Silakan reset izin di pengaturan situs browser Anda.";
        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            msg = "Kamera tidak ditemukan pada perangkat ini.";
        }
        alert(msg);
    }
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

function takeSnapshot() {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const dataURL = canvas.toDataURL('image/jpeg');
    submitAbsensi(dataURL);
}

// --- QR Logic ---
function startQrScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    
    html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
        stopQrScanner();
        submitAbsensi(null, decodedText); 
    })
    .catch(err => {
        console.error("Error starting QR scanner", err);
        let msg = "Gagal memulai scanner QR.";
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
             msg += " Kemungkinan karena koneksi tidak aman (HTTP).";
        }
        alert(msg);
    });
}

function stopQrScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
        }).catch(err => console.error(err));
    }
}

// --- Submit ---
async function submitAbsensi(fotoBase64 = null, qrContent = null) {
    if (!userLocation) {
        alert("Lokasi belum terdeteksi. Mohon tunggu sebentar atau aktifkan GPS.");
        return;
    }

    const formData = new FormData();
    formData.append('action', currentAction);
    formData.append('lokasi', userLocation);
    if (fotoBase64) formData.append('foto', fotoBase64);
    if (qrContent) formData.append('qr_content', qrContent);

    // Show loading
    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const response = await fetch(`${basePath}/api/hr/portal/absensi`, {
            method: 'POST',
            body: formData
        });
        
        let result;
        try {
            result = await response.json();
        } catch (e) {
            throw new Error("Gagal memproses respon server. Kemungkinan terjadi error internal.");
        }

        if (result.success) {
            Swal.fire('Berhasil!', result.message, 'success').then(() => {
                closeAbsenModal();
                loadTodayStatus();
                loadHistory();
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire('Gagal', error.message, 'error');
    }
}

// Fungsi Preview Foto
window.viewPhoto = function(url, title) {
    Swal.fire({
        title: title,
        imageUrl: url,
        imageAlt: 'Foto Absensi',
        imageHeight: 400,
        confirmButtonText: 'Tutup'
    });
};