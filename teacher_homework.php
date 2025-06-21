<?php
// filepath: c:\xampp\htdocs\Курсовая\teacher_homework.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Получаем все домашние задания для уроков этого учителя
$stmt = $pdo->prepare("
    SELECT 
        h.id, h.student_id, h.lesson_id, h.subject, h.task, h.file_path, h.mark,
        s.lesson_date, s.lesson_time,
        u.login AS student_login
    FROM homework h
    JOIN schedule s ON h.lesson_id = s.id
    JOIN users u ON h.student_id = u.id
    WHERE s.teacher_id = ?
    ORDER BY s.lesson_date DESC, s.lesson_time DESC
");
$stmt->execute([$teacher_id]);
$homeworks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Домашние задания учеников</title>
    <link rel="stylesheet" href="teacher_homework.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="profile-btn" style="text-align:right; margin: 18px 24px 0 0;">
        <a href="teacher_dashboard.php">← В кабинет</a>
    </div>
    <main>
        <h2>Домашние задания учеников</h2>
        <table class="marks-table">
            <tr>
                <th>Дата</th>
                <th>Время</th>
                <th>Ученик</th>
                <th>Предмет</th>
                <th>Задание</th>
                <th>Файл</th>
                <th>Оценка</th>
            </tr>
            <?php foreach ($homeworks as $hw): ?>
            <tr>
                <td><?= htmlspecialchars(date('d.m.Y', strtotime($hw['lesson_date']))) ?></td>
                <td><?= htmlspecialchars(substr($hw['lesson_time'],0,5)) ?></td>
                <td><?= htmlspecialchars($hw['student_login']) ?></td>
                <td><?= htmlspecialchars($hw['subject']) ?></td>
                <td style="text-align:left;"><?= htmlspecialchars($hw['task']) ?></td>
                <td>
                    <?php if (!empty($hw['file_path'])): ?>
                        <a href="<?= htmlspecialchars($hw['file_path']) ?>" target="_blank" class="download-link">Скачать</a>
                    <?php else: ?>
                        <span style="color:#aaa;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="set_homework_mark.php" style="display:flex;gap:6px;align-items:center;">
                        <input type="hidden" name="homework_id" value="<?= htmlspecialchars($hw['id']) ?>">
                        <input type="number" name="mark" min="1" max="5" value="<?= $hw['mark'] !== null ? htmlspecialchars($hw['mark']) : '' ?>" style="width:48px;">
                        <button type="submit" class="attach-btn" style="padding:2px 10px;">OK</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($homeworks)): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;">Нет домашних заданий</td></tr>
            <?php endif; ?>
        </table>
    </main>
</body>
</html>