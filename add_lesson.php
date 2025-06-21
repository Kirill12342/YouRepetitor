<?php
// filepath: c:\xampp\htdocs\Курсовая\add_lesson.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_date = $_POST['lesson_date'];
    $lesson_time = $_POST['lesson_time'];
    $subject = trim($_POST['subject']);
    $comment = trim($_POST['comment']);
    $assign_type = $_POST['assign_type'];

    if ($assign_type === 'class') {
        $class = $_POST['class'];
        // Получаем всех студентов класса
        $stmt = $pdo->prepare("SELECT id FROM users WHERE class = ? AND role = 'student'");
        $stmt->execute([$class]);
        $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($students as $student_id) {
            $stmt2 = $pdo->prepare("INSERT INTO schedule (teacher_id, student_id, lesson_date, lesson_time, subject, comment) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt2->execute([$teacher_id, $student_id, $lesson_date, $lesson_time, $subject, $comment]);
        }
    } else {
        $student_id = intval($_POST['student_id']);
        $stmt = $pdo->prepare("INSERT INTO schedule (teacher_id, student_id, lesson_date, lesson_time, subject, comment) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$teacher_id, $student_id, $lesson_date, $lesson_time, $subject, $comment]);
    }
    header("Location: teacher_dashboard.php");
    exit;
}
?>