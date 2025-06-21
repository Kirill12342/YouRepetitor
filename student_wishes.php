<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Получаем пожелания и оценки по урокам этого преподавателя
$stmt = $pdo->prepare("
    SELECT lf.*, u.login AS student_login, s.lesson_date, s.subject
    FROM lesson_feedback lf
    JOIN schedule s ON lf.lesson_id = s.id
    JOIN users u ON lf.student_id = u.id
    WHERE s.teacher_id = ?
    ORDER BY s.lesson_date DESC
");
$stmt->execute([$teacher_id]);
$feedbacks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пожелания учеников</title>
    <link rel="stylesheet" href="teacher_dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <main>
        <h2>Пожелания и оценки от учеников</h2>
        <table class="marks-table">
            <tr>
                <th>Ученик</th>
                <th>Дата</th>
                <th>Предмет</th>
                <th>Оценка преподавателя</th>
                <th>Пожелание</th>
            </tr>
            <?php foreach ($feedbacks as $fb): ?>
            <tr>
                <td><?= htmlspecialchars($fb['student_login']) ?></td>
                <td><?= htmlspecialchars(date('d.m.Y', strtotime($fb['lesson_date'])) ) ?></td>
                <td><?= htmlspecialchars($fb['subject']) ?></td>
                <td><?= htmlspecialchars($fb['teacher_mark']) ?></td>
                <td><?= htmlspecialchars($fb['wish']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <form action="teacher_dashboard.php" method="get" style="margin-top:24px;">
            <button type="submit" class="back-btn">← К расписанию</button>
        </form>
    </main>
</body>
</html>