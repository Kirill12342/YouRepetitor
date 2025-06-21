

function openAttachModal(homeworkId) {
    document.getElementById('modal_homework_id').value = homeworkId;
    document.getElementById('attachModal').style.display = 'flex';
}
function closeAttachModal() {
    document.getElementById('attachModal').style.display = 'none';
}
// Закрытие по клику вне окна
document.addEventListener('click', function(e){
    var modal = document.getElementById('attachModal');
    if (modal && e.target === modal) modal.style.display = 'none';
});