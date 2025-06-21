document.addEventListener('DOMContentLoaded', function() {
    const authBtn = document.getElementById('authBtn');
    const authModal = document.getElementById('authModal');
    const closeModal = document.getElementById('closeModal');

    authBtn.onclick = function() {
        authModal.style.display = 'flex';
    };
    closeModal.onclick = function() {
        authModal.style.display = 'none';
    };
});


function openRequestModal() {
    document.getElementById('requestModal').style.display = 'flex';
}
function closeRequestModal() {
    document.getElementById('requestModal').style.display = 'none';
}
document.addEventListener('click', function(e){
    var modal = document.getElementById('requestModal');
    if (modal && e.target === modal) modal.style.display = 'none';
});