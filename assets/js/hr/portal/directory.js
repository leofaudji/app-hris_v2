function initPortalDirectoryPage() {
    loadDirectoryDivisiOptions();
    loadDirectoryData();

    const searchInput = document.getElementById('dir-search-input');
    const divisiFilter = document.getElementById('dir-filter-divisi');

    if (searchInput) {
        searchInput.addEventListener('input', debounce(loadDirectoryData, 500));
    }
    if (divisiFilter) {
        divisiFilter.addEventListener('change', loadDirectoryData);
    }
}

async function loadDirectoryDivisiOptions() {
    const select = document.getElementById('dir-filter-divisi');
    if (!select) return;

    try {
        const response = await fetch(`${basePath}/api/hr/divisi`);
        const result = await response.json();
        if (result.success) {
            result.data.forEach(divisi => {
                select.insertAdjacentHTML('beforeend', `<option value="${divisi.id}">${divisi.nama_divisi}</option>`);
            });
        }
    } catch (error) {
        console.error('Error loading divisi:', error);
    }
}

async function loadDirectoryData() {
    const container = document.getElementById('directory-grid');
    const search = document.getElementById('dir-search-input')?.value || '';
    const divisiId = document.getElementById('dir-filter-divisi')?.value || '';

    if (!container) return;

    container.innerHTML = '<div class="col-span-full flex flex-col items-center justify-center py-12 text-gray-500"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary mb-3"></div><p>Memuat data direktori...</p></div>';

    try {
        const response = await fetch(`${basePath}/api/hr/portal/directory?search=${encodeURIComponent(search)}&divisi_id=${divisiId}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(emp => {
                const photoUrl = emp.foto_profil ? `${basePath}/${emp.foto_profil}` : null;
                const initials = emp.nama_lengkap.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                
                // Generate random gradient based on ID for background
                const gradients = [
                    'from-blue-500 to-cyan-400',
                    'from-purple-500 to-pink-400',
                    'from-emerald-500 to-teal-400',
                    'from-orange-500 to-amber-400',
                    'from-indigo-500 to-purple-400',
                    'from-rose-500 to-red-400'
                ];
                const gradientClass = gradients[emp.id % gradients.length];

                const photoHtml = photoUrl 
                    ? `<img src="${photoUrl}" alt="${emp.nama_lengkap}" class="w-24 h-24 rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-md bg-white dark:bg-gray-800">`
                    : `<div class="w-24 h-24 rounded-full bg-white dark:bg-gray-800 border-4 border-white dark:border-gray-800 shadow-md flex items-center justify-center text-2xl font-bold text-gray-600 dark:text-gray-300">${initials}</div>`;

                return `
                    <div class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-300 overflow-hidden flex flex-col h-full">
                        <!-- Header / Cover -->
                        <div class="h-24 bg-gradient-to-r ${gradientClass} relative">
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-1 bg-white/20 backdrop-blur-sm rounded text-[10px] font-semibold text-white border border-white/10 shadow-sm">
                                    ${emp.nama_kantor || 'Kantor Pusat'}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Profile Content -->
                        <div class="px-6 pb-6 flex-1 flex flex-col relative">
                            <!-- Avatar -->
                            <div class="-mt-12 mb-4 flex justify-center">
                                ${photoHtml}
                            </div>

                            <!-- Info -->
                            <div class="text-center mb-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-primary transition-colors truncate" title="${emp.nama_lengkap}">${emp.nama_lengkap}</h3>
                                <p class="text-sm text-primary font-medium mb-1 truncate" title="${emp.nama_jabatan}">${emp.nama_jabatan || '-'}</p>
                                <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 mt-1">
                                    ${emp.nama_divisi || '-'}
                                </div>
                                ${emp.nip ? `<p class="text-xs text-gray-400 mt-2 font-mono">${emp.nip}</p>` : ''}
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-gray-100 dark:border-gray-700 my-2"></div>

                            <!-- Contact Actions -->
                            <div class="mt-auto space-y-2 pt-2">
                                ${emp.email ? `
                                    <a href="mailto:${emp.email}" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors gap-2 group/btn">
                                        <i class="bi bi-envelope text-gray-400 group-hover/btn:text-primary transition-colors"></i>
                                        <span class="truncate">${emp.email}</span>
                                    </a>
                                ` : ''}
                                
                                ${emp.no_hp ? `
                                    <a href="https://wa.me/${emp.no_hp.replace(/^0/, '62').replace(/\D/g,'')}" target="_blank" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors gap-2 shadow-sm">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Chat WhatsApp</span>
                                    </a>
                                ` : ''}

                                ${!emp.email && !emp.no_hp ? `
                                    <div class="text-center text-xs text-gray-400 italic py-2">
                                        Tidak ada informasi kontak
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="col-span-full text-center py-12 text-gray-500"><i class="bi bi-search text-4xl mb-2 block opacity-50"></i>Tidak ada karyawan ditemukan.</div>';
        }
    } catch (error) {
        console.error('Error loading directory:', error);
        container.innerHTML = '<div class="col-span-full text-center py-12 text-red-500">Gagal memuat data direktori.</div>';
    }
}