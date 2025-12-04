/**
 * SweetAlert Delete Confirmation
 * Provides a confirmation dialog before form submission for delete actions
 */
function confirmDelete(formId, title, text) {
    Swal.fire({
        title: title || 'Yakin ingin menghapus?',
        text: text || 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d63939',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
