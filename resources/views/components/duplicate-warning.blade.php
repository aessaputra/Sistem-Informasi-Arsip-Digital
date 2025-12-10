@props([
    'existingDocument' => null,
    'similarityScore' => 0,
    'detectionMethod' => '',
    'isUpdate' => false
])

@if(session('duplicate_detected'))
{{-- Hidden div for SweetAlert data (JavaScript will use this) --}}
<div id="duplicate-data" style="display: none;" 
     data-existing-document="{{ json_encode($existingDocument) }}"
     data-similarity-score="{{ $similarityScore }}"
     data-detection-method="{{ $detectionMethod }}"
     data-is-update="{{ $isUpdate ? 'true' : 'false' }}">
</div>

{{-- Fallback Bootstrap Alert (hidden by default, shown if JavaScript fails) --}}
<noscript>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 9v2m0 4v.01"></path>
                <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path>
            </svg>
        </div>
        <div class="flex-fill">
            <h4 class="alert-title">Dokumen Duplikat Terdeteksi</h4>
            <div class="text-secondary">
                File yang Anda upload {{ $isUpdate ? 'untuk update' : '' }} mirip dengan dokumen yang sudah ada dalam sistem.
            </div>
        </div>
    </div>
</div>
</noscript>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    showDuplicateAlert();
});

function formatDate(dateString) {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${day} ${month} ${year}`;
    } catch (e) {
        return dateString;
    }
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                       'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${day} ${month} ${year}, ${hours}:${minutes}`;
    } catch (e) {
        return dateString;
    }
}

function getMethodLabel(method) {
    const labels = {
        'file_hash': 'Hash File Identik',
        'file_size_metadata': 'Ukuran File & Metadata',
        'nomor_surat': 'Nomor Surat Sama',
        'metadata': 'Metadata Serupa',
        'content_similarity': 'Konten Serupa'
    };
    return labels[method] || method || '-';
}

function getMethodBadgeClass(method) {
    const classes = {
        'file_hash': 'bg-danger-lt text-danger',
        'file_size_metadata': 'bg-warning-lt text-warning',
        'nomor_surat': 'bg-danger-lt text-danger',
        'metadata': 'bg-azure-lt text-azure',
        'content_similarity': 'bg-purple-lt text-purple'
    };
    return classes[method] || 'bg-secondary-lt text-secondary';
}

function isDarkMode() {
    return document.body.getAttribute('data-bs-theme') === 'dark' || 
           document.documentElement.getAttribute('data-bs-theme') === 'dark';
}

function showDuplicateAlert() {
    const dataDiv = document.getElementById('duplicate-data');
    if (!dataDiv) return;
    
    const existingDoc = JSON.parse(dataDiv.dataset.existingDocument || 'null');
    const similarityScore = parseFloat(dataDiv.dataset.similarityScore || 0) * 100;
    const detectionMethod = dataDiv.dataset.detectionMethod || '';
    const isUpdate = dataDiv.dataset.isUpdate === 'true';
    const darkMode = isDarkMode();
    
    // Tabler icons as SVG
    const icons = {
        warning: `<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>`,
        document: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M9 9l1 0"/><path d="M9 13l6 0"/><path d="M9 17l6 0"/></svg>`,
        eye: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>`,
        refresh: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>`,
        x: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>`,
        save: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>`,
        target: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M12 12m-5 0a5 5 0 1 0 10 0a5 5 0 1 0 -10 0"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/></svg>`,
        search: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l.01 0"/><path d="M11 12l1 0l0 4l1 0"/></svg>`
    };

    const cardBg = darkMode ? 'bg-dark-lt' : 'bg-light';
    const cardBorder = darkMode ? 'border-secondary' : 'border';
    const textMuted = darkMode ? 'text-secondary' : 'text-muted';
    
    let htmlContent = `
        <div class="text-start">
            <p class="mb-3 ${textMuted}">File yang Anda upload ${isUpdate ? 'untuk update' : ''} mirip dengan dokumen yang sudah ada dalam sistem.</p>
            
            ${existingDoc ? `
            <div class="card ${cardBorder} mb-3">
                <div class="card-header ${cardBg}">
                    <h3 class="card-title d-flex align-items-center mb-0">
                        ${icons.document}
                        Detail Dokumen yang Sudah Ada
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="${textMuted} small">Nomor Surat</span>
                                <div class="fw-medium">${existingDoc.nomor_surat || '-'}</div>
                            </div>
                            <div class="mb-2">
                                <span class="${textMuted} small">Perihal</span>
                                <div class="fw-medium">${existingDoc.perihal || '-'}</div>
                            </div>
                            <div class="mb-2">
                                <span class="${textMuted} small">Tanggal Surat</span>
                                <div class="fw-medium">${formatDate(existingDoc.tanggal_surat)}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="${textMuted} small">Dari/Tujuan</span>
                                <div class="fw-medium">${existingDoc.dari || existingDoc.tujuan || '-'}</div>
                            </div>
                            <div class="mb-2">
                                <span class="${textMuted} small">Diinput oleh</span>
                                <div class="fw-medium">${existingDoc.petugas?.name || '-'}</div>
                            </div>
                            <div class="mb-2">
                                <span class="${textMuted} small">Tanggal Input</span>
                                <div class="fw-medium">${formatDateTime(existingDoc.created_at)}</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-cyan-lt text-cyan d-inline-flex align-items-center">
                            ${icons.target}
                            Kemiripan: ${similarityScore.toFixed(1)}%
                        </span>
                        <span class="badge ${getMethodBadgeClass(detectionMethod)} d-inline-flex align-items-center">
                            ${icons.search}
                            Metode: ${getMethodLabel(detectionMethod)}
                        </span>
                    </div>
                </div>
            </div>
            ` : ''}
            
            <div class="alert alert-info mb-0">
                <div class="d-flex">
                    <div class="me-2">${icons.info}</div>
                    <div>
                        <h4 class="alert-title">Pilihan Anda</h4>
                        <ul class="mb-0 ps-3">
                            <li><strong>Lihat Dokumen:</strong> Buka dokumen asli di tab baru</li>
                            <li><strong>Ganti File:</strong> Pilih file yang berbeda</li>
                            <li><strong>Batalkan:</strong> Batalkan upload ini</li>
                            <li><strong>Tetap Simpan:</strong> Simpan dengan penanda duplikat</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        title: 'Dokumen Duplikat Terdeteksi',
        html: htmlContent,
        icon: 'warning',
        width: '700px',
        showCancelButton: false,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        background: darkMode ? '#1e2633' : '#ffffff',
        color: darkMode ? '#f8fafc' : '#1e293b',
        customClass: {
            popup: 'duplicate-alert-popup'
        },
        footer: `
            <div class="d-flex flex-wrap gap-2 justify-content-center w-100">
                ${existingDoc ? `
                <button type="button" class="btn btn-outline-cyan" onclick="viewExistingDocument(${existingDoc.id})">
                    ${icons.eye} Lihat Dokumen
                </button>
                ` : ''}
                
                ${isUpdate ? `
                <button type="button" class="btn btn-warning" onclick="replaceDuplicateFile()">
                    ${icons.refresh} Ganti File
                </button>
                ` : ''}
                
                <button type="button" class="btn btn-outline-secondary" onclick="skipDuplicateUpload()">
                    ${icons.x} Batalkan
                </button>
                
                <button type="button" class="btn btn-danger" onclick="forceSaveDuplicate()">
                    ${icons.save} Tetap Simpan
                </button>
            </div>
        `
    });
}

function viewExistingDocument(docId) {
    const routeName = window.location.pathname.includes('surat-masuk') ? 'surat-masuk' : 'surat-keluar';
    const url = `/${routeName}/${docId}`;
    window.open(url, '_blank');
}

function replaceDuplicateFile() {
    Swal.close();
    const fileInput = document.querySelector('input[type="file"][name="file_path"]');
    if (fileInput) {
        fileInput.value = '';
        fileInput.click();
    }
    
    const darkMode = isDarkMode();
    Swal.fire({
        title: 'Pilih File Baru',
        text: 'Silakan pilih file yang berbeda untuk diupload.',
        icon: 'info',
        timer: 3000,
        showConfirmButton: false,
        background: darkMode ? '#1e2633' : '#ffffff',
        color: darkMode ? '#f8fafc' : '#1e293b'
    });
}

function skipDuplicateUpload() {
    const darkMode = isDarkMode();
    Swal.fire({
        title: 'Yakin batalkan upload?',
        text: 'File yang dipilih akan dihapus dan form akan direset.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#206bc4',
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true,
        background: darkMode ? '#1e2633' : '#ffffff',
        color: darkMode ? '#f8fafc' : '#1e293b'
    }).then((result) => {
        if (result.isConfirmed) {
            const fileInput = document.querySelector('input[type="file"][name="file_path"]');
            if (fileInput) {
                fileInput.value = '';
            }
            
            Swal.fire({
                title: 'Upload Dibatalkan',
                text: 'File telah dihapus dari form.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                background: darkMode ? '#1e2633' : '#ffffff',
                color: darkMode ? '#f8fafc' : '#1e293b'
            });
        }
    });
}

function forceSaveDuplicate() {
    const darkMode = isDarkMode();
    Swal.fire({
        title: 'Tetap simpan sebagai duplikat?',
        html: `
            <p>Dokumen akan disimpan dengan penanda <strong>duplikat</strong>.</p>
            <div class="alert alert-warning">
                <small>Sistem akan mencatat bahwa ini adalah dokumen duplikat untuk audit trail.</small>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d63939',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, tetap simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        background: darkMode ? '#1e2633' : '#ffffff',
        color: darkMode ? '#f8fafc' : '#1e293b'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.querySelector('form');
            if (form) {
                let forceSaveInput = document.createElement('input');
                forceSaveInput.type = 'hidden';
                forceSaveInput.name = 'force_save';
                forceSaveInput.value = '1';
                form.appendChild(forceSaveInput);

                let duplicateOverrideInput = document.createElement('input');
                duplicateOverrideInput.type = 'hidden';
                duplicateOverrideInput.name = 'is_duplicate_override';
                duplicateOverrideInput.value = '1';
                form.appendChild(duplicateOverrideInput);

                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Dokumen sedang disimpan dengan penanda duplikat.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    background: darkMode ? '#1e2633' : '#ffffff',
                    color: darkMode ? '#f8fafc' : '#1e293b',
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                form.submit();
            }
        }
    });
}
</script>

<style>
.duplicate-alert-popup {
    font-size: 14px;
}
.duplicate-alert-popup .card {
    margin-bottom: 0;
}
.duplicate-alert-popup .card-header {
    padding: 0.75rem 1rem;
}
.duplicate-alert-popup .card-body {
    padding: 1rem;
}
.duplicate-alert-popup .swal2-footer {
    border-top: 1px solid var(--tblr-border-color, #e6e7e9);
    padding: 1rem;
}
.duplicate-alert-popup .btn {
    font-size: 13px;
    padding: 0.5rem 0.75rem;
}
.duplicate-alert-popup .alert {
    font-size: 13px;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .duplicate-alert-popup .card {
    background-color: #1e2633;
    border-color: #3a4555;
}
[data-bs-theme="dark"] .duplicate-alert-popup .card-header {
    background-color: #243049;
    border-color: #3a4555;
}
[data-bs-theme="dark"] .duplicate-alert-popup .swal2-footer {
    border-color: #3a4555;
}
</style>
@endpush
@endif
