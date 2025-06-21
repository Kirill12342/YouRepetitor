<?php
// filepath: c:\xampp\htdocs\Курсовая\set_homework_mark.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

if (isset($_POST['homework_id'], $_POST['mark'])) {
    $homework_id = intval($_POST['homework_id']);
    $mark = intval($_POST['mark']);
    // Можно добавить проверку, что этот homework_id действительно относится к уроку этого учителя
    $stmt = $pdo->prepare("UPDATE homework SET mark = ? WHERE id = ?");
    $stmt->execute([$mark, $homework_id]);
}
header("Location: teacher_homework.php");
exit;