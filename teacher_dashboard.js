document.addEventListener('DOMContentLoaded', function() {
    const openCreateClass = document.getElementById('openCreateClass');
    const openAddStudent = document.getElementById('openAddStudent');
    const modalCreateClass = document.getElementById('modalCreateClass');
    const modalAddStudent = document.getElementById('modalAddStudent');
    const closeButtons = document.querySelectorAll('.close-modal');

    if (openCreateClass) {
        openCreateClass.onclick = function() {
            modalCreateClass.style.display = 'flex';
        };
    }
    if (openAddStudent) {
        openAddStudent.onclick = function() {
            modalAddStudent.style.display = 'flex';
        };
    }
    closeButtons.forEach(btn => {
        btn.onclick = function() {
            btn.closest('.modal-bg').style.display = 'none';
        };
    });
    window.onclick = function(e) {
        document.querySelectorAll('.modal-bg').forEach(modal => {
            if (e.target === modal) modal.style.display = 'none';
        });
    };
});


function openLessonModal() {
    document.getElementById('lessonModal').style.display = 'flex';
}
function closeLessonModal() {
    document.getElementById('lessonModal').style.display = 'none';
}
document.addEventListener('click', function(e){
    var modal = document.getElementById('lessonModal');
    if (modal && e.target === modal) modal.style.display = 'none';
});