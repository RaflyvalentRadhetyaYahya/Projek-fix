/* ==========================================
   DOSEN - Page Specific Scripts
   ========================================== */

// Edit Modal Logic
const editBtns = document.querySelectorAll('.btn-edit');
const editModal = document.getElementById('editModal');
const btnTutupEdit = document.getElementById('btnTutupEdit');
const btnBatalEdit = document.getElementById('btnBatalEdit');
const btnSimpanEdit = document.getElementById('btnSimpanEdit');

editBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        editModal.classList.add('show');
    });
});

btnTutupEdit.addEventListener('click', () => editModal.classList.remove('show'));
btnBatalEdit.addEventListener('click', () => editModal.classList.remove('show'));
btnSimpanEdit.addEventListener('click', () => {
    editModal.classList.remove('show');
    alert('Data dosen berhasil diperbarui!');
});

editModal.addEventListener('click', function(e) {
    if (e.target === editModal) editModal.classList.remove('show');
});

// Delete Modal Logic
const deleteBtns = document.querySelectorAll('.btn-delete');
const deleteModal = document.getElementById('deleteModal');
const btnBatalHapus = document.getElementById('btnBatalHapus');
const btnKonfirmasiHapus = document.getElementById('btnKonfirmasiHapus');

deleteBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        deleteModal.classList.add('show');
    });
});

btnBatalHapus.addEventListener('click', () => deleteModal.classList.remove('show'));
btnKonfirmasiHapus.addEventListener('click', () => {
    deleteModal.classList.remove('show');
    alert('Data dosen berhasil dihapus!');
});

deleteModal.addEventListener('click', function(e) {
    if (e.target === deleteModal) deleteModal.classList.remove('show');
});
