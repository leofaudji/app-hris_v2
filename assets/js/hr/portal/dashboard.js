function initPortalDashboardPage() {
    if (document.getElementById('portal-nama-karyawan')) {
        fetch(`${basePath}/api/hr/portal/dashboard`)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;
                    document.getElementById('portal-nama-karyawan').textContent = data.nama_karyawan;
                    document.getElementById('portal-sisa-cuti').textContent = data.sisa_cuti;
                    document.getElementById('portal-kehadiran').textContent = data.kehadiran_bulan_ini;
                    document.getElementById('portal-cuti-pending').textContent = data.cuti_pending;
                } else {
                    showToast(res.message, 'error');
                }
            });
    }
}