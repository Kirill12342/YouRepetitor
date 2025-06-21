<?php
// filepath: c:\xampp\htdocs\Курсовая\student_dashboard.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login_student.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Массив дней недели на русском
$days = [
    'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'
];
$week_lessons = [];
foreach ($days as $day) $week_lessons[$day] = [];

// Получаем расписание и группируем по дню недели на русском

$stmt = $pdo->prepare("SELECT s.*, t.login AS teacher_login, u.login AS student_login 
    FROM schedule s 
    JOIN users t ON s.teacher_id = t.id 
    JOIN users u ON s.student_id = u.id
    WHERE s.student_id = ?
    ORDER BY s.lesson_date, s.lesson_time");
$stmt->execute([$student_id]);
while ($row = $stmt->fetch()) {
    $date = strtotime($row['lesson_date']);
    $day_num = date('N', $date) - 1; // 0 - понедельник
    $day = $days[$day_num];
    $week_lessons[$day][] = $row;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой дневник</title>
    <link rel="stylesheet" href="student_dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <!-- Кнопка профиля -->

    <div class="profile-btn" style="text-align:right; margin: 18px 24px 0 0;">
        <a href="student_homework.php" class="main-action-btn" style="margin-left:12px;">Домашнее задание</a>
        <a href="profile.php">Профиль</a>
    </div>
    
    <main>
        <h2>Моё расписание</h2>
        <div class="schedule-week">
            <?php foreach ($week_lessons as $day => $lessons): ?>
                <div class="schedule-day">
                    <strong><?= htmlspecialchars($day) ?></strong>
                    <ul>
                        <?php foreach ($lessons as $lesson): ?>
                            <li>
                                <div class="lesson-header-row">
                                    <span class="lesson-time"><?= htmlspecialchars(substr($lesson['lesson_time'],0,5)) ?></span>
                                    <span class="lesson-subject"><?= htmlspecialchars($lesson['subject']) ?></span>
                                </div>
                                <div style="font-size:0.97em; color:#4e54c8; margin-bottom:2px;">
                                    Преподаватель: <?= htmlspecialchars($lesson['teacher_login']) ?>
                                </div>
                                <div style="margin-top:6px;">
                                    <a href="lesson_student.php?id=<?= $lesson['id'] ?>" class="lesson-link-btn">К уроку</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>