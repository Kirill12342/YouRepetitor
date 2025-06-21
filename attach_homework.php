<?php
// filepath: c:\xampp\htdocs\Курсовая\attach_homework.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login_student.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['homework_id'])) {
    $homework_id = intval($_POST['homework_id']);
    $student_id = $_SESSION['user_id'];

    // Проверяем, что это ДЗ этого студента
    $stmt = $pdo->prepare("SELECT * FROM homework WHERE id = ? AND student_id = ?");
    $stmt->execute([$homework_id, $student_id]);
    $hw = $stmt->fetch();
    if (!$hw) {
        header("Location: student_homework.php");
        exit;
    }

    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['doc', 'docx', 'pdf', 'txt'];
    if (in_array($ext, $allowed) && $file['size'] <= 10*1024*1024) {
        $new_name = uniqid('hw_'.$homework_id.'_').'.'.$ext;
        $target = $upload_dir . $new_name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Сохраняем путь к файлу
            $stmt = $pdo->prepare("UPDATE homework SET file_path = ? WHERE id = ?");
            $stmt->execute([$target, $homework_id]);
        }
    }
}

header("Location: student_homework.php");
exit;