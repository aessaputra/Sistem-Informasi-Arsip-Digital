@props([
    'existingDocument' => null,
    'similarityScore' => 0,
    'detectionMethod' => '',
    'isUpdate' => false
])

@if(session('duplicate_detected'))
{{-- Hidden div for SweetAlert data --}}
<div id="duplicate-data" style="display: none;" 
     data-existing-document="{{ json_encode($existingDocument) }}"
     data-similarity-score="{{ $similarityScore }}"
     data-detection-method="{{ $detectionMethod }}"
     data-is-update="{{ $isUpdate ? 'true' : 'false' }}">
</div>

{{-- Fallback for noscript --}}
<noscript>
<div class="alert alert-warning" role="alert">
    <strong>Dokumen Duplikat Terdeteksi!</strong> File yang Anda upload mirip dengan dokumen yang sudah ada.
</div>
</noscript>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataDiv = document.getElementById('duplicate-data');
    if (!dataDiv) return;
    
    const existingDoc = JSON.parse(dataDiv.dataset.existingDocument || 'null');
    const similarityScore = (parseFloat(dataDiv.dataset.similarityScore || 0) * 100).toFixed(0);
    const detectionMethod = dataDiv.dataset.detectionMethod || '';
    const darkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    
    // Method labels
    const methodLabels = {
        'file_hash': 'Hash Identik',
        'nomor_surat': 'Nomor Surat Sama',
        'metadata': 'Metadata Serupa'
    };
    const methodLabel = methodLabels[detectionMethod] || detectionMethod || '-';
    
    // Build info HTML
    let infoHtml = `
        <p class="mb-3" style="color: #666;">File ini mirip dengan dokumen yang sudah ada dalam sistem.</p>
        <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 12px;">
            <span style="background: #e8f4f8; color: #0ca678; padding: 4px 10px; border-radius: 4px; font-size: 13px;">
                <strong>Kemiripan:</strong> ${similarityScore}%
            </span>
            <span style="background: #fff3e0; color: #f76707; padding: 4px 10px; border-radius: 4px; font-size: 13px;">
                <strong>Metode:</strong> ${methodLabel}
            </span>
        </div>
    `;
    
    if (existingDoc) {
        infoHtml += `
            <div class="datagrid" style="text-align: left; margin-top: 8px;">
                <div class="datagrid-item">
                    <div class="datagrid-title">Nomor Surat</div>
                    <div class="datagrid-content">${existingDoc.nomor_surat || '-'}</div>
                </div>
                <div class="datagrid-item">
                    <div class="datagrid-title">Perihal</div>
                    <div class="datagrid-content">${existingDoc.perihal || '-'}</div>
                </div>
            </div>
        `;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Dokumen Duplikat Terdeteksi',
        html: infoHtml,
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'Tetap Simpan',
        denyButtonText: 'Lihat Dokumen',
        cancelButtonText: 'Batalkan',
        confirmButtonColor: '#d63939',
        denyButtonColor: '#206bc4',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        allowOutsideClick: false,
        background: darkMode ? '#1e2633' : '#ffffff',
        color: darkMode ? '#f8fafc' : '#1e293b'
    }).then((result) => {
        if (result.isConfirmed) {
            // Force save
            const form = document.querySelector('form');
            if (form) {
                const input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'force_save';
                input1.value = '1';
                form.appendChild(input1);

                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'is_duplicate_override';
                input2.value = '1';
                form.appendChild(input2);

                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    background: darkMode ? '#1e2633' : '#ffffff',
                    color: darkMode ? '#f8fafc' : '#1e293b',
                    didOpen: () => Swal.showLoading()
                });
                
                form.submit();
            }
        } else if (result.isDenied && existingDoc) {
            // View existing document
            const routeName = window.location.pathname.includes('surat-masuk') ? 'surat-masuk' : 'surat-keluar';
            window.open(`/${routeName}/${existingDoc.id}`, '_blank');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Cancel upload
            const fileInput = document.querySelector('input[type="file"][name="file_path"]');
            if (fileInput) fileInput.value = '';
            
            Swal.fire({
                icon: 'info',
                title: 'Dibatalkan',
                text: 'File telah dihapus dari form.',
                timer: 1500,
                showConfirmButton: false,
                background: darkMode ? '#1e2633' : '#ffffff',
                color: darkMode ? '#f8fafc' : '#1e293b'
            });
        }
    });
});
</script>
@endpush
@endif
