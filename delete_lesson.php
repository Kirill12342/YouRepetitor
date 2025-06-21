<?php
// delete_lesson.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

if (isset($_POST['lesson_id'])) {
    $lesson_id = intval($_POST['lesson_id']);
    // Удаляем только тот урок, который принадлежит этому учителю
    $stmt = $pdo->prepare("DELETE FROM schedule WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$lesson_id, $_SESSION['user_id']]);
}
header("Location: teacher_dashboard.php");
exit;