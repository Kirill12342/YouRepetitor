<?php
// filepath: c:\xampp\htdocs\Курсовая\student_homework.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login_student.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Получаем все домашние задания для студента с нужными полями
$stmt = $pdo->prepare("
    SELECT 
        h.id, 
        h.lesson_id, 
        h.subject, 
        h.task, 
        h.file_path, 
        h.mark,
        s.lesson_date, 
        s.lesson_time, 
        u.login AS teacher_login
    FROM 
        homework h
    JOIN 
        schedule s ON h.lesson_id = s.id
    JOIN 
        users u ON s.teacher_id = u.id
    WHERE 
        h.student_id = ?
    ORDER BY 
        s.lesson_date DESC, s.lesson_time DESC
");
$stmt->execute([$student_id]);
$homeworks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Домашние задания</title>
    <link rel="stylesheet" href="student_homework.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="profile-btn" style="text-align:right; margin: 18px 24px 0 0;">
        <a href="student_dashboard.php">← К расписанию</a>
    </div>
    <main>
        <h2>Все домашние задания</h2>
        <table class="marks-table">
            <tr>
                <th>Дата</th>
                <th>Время</th>
                <th>Предмет</th>
                <th>Преподаватель</th>
                <th>Задание</th>
                <th>Оценка</th>
                <th>Прикрепить ДЗ</th>
            </tr>
            <?php foreach ($homeworks as $hw): ?>
            <tr>
                <td><?= htmlspecialchars(date('d.m.Y', strtotime($hw['lesson_date']))) ?></td>
                <td><?= htmlspecialchars(substr($hw['lesson_time'],0,5)) ?></td>
                <td><?= htmlspecialchars($hw['subject']) ?></td>
                <td><?= htmlspecialchars($hw['teacher_login']) ?></td>
                <td style="text-align:left;"><?= htmlspecialchars($hw['task']) ?></td>
                <td>
                    <?= isset($hw['mark']) && $hw['mark'] !== null ? htmlspecialchars($hw['mark']) : '<span style="color:#aaa;">—</span>' ?>
                </td>
                <td>
                    <button type="button" class="attach-btn" onclick="openAttachModal(<?= htmlspecialchars($hw['id']) ?>)">Прикрепить</button>
                    <?php if (!empty($hw['file_path'])): ?>
                        <a href="<?= htmlspecialchars($hw['file_path']) ?>" target="_blank" class="download-link">Скачать</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($homeworks)): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;">Нет домашних заданий</td></tr>
            <?php endif; ?>
        </table>

    </main>
    <div id="attachModal" class="modal-bg" style="display:none;">
        <div class="modal-window">
            <form method="post" action="attach_homework.php" enctype="multipart/form-data">
                <input type="hidden" name="homework_id" id="modal_homework_id">
                <h3>Прикрепить файл</h3>
                <input type="file" name="file" accept=".doc,.docx,.pdf,.txt" required style="margin-bottom:16px;">
                <div style="display:flex; gap:12px; justify-content:center;">
                    <button type="submit" class="attach-btn">Загрузить</button>
                    <button type="button" class="attach-btn" style="background:#eee;color:#4e54c8;" onclick="closeAttachModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="student_homework.js"></script>
</body>
</html>