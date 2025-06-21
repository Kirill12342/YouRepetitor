<?php
// filepath: c:\xampp\htdocs\Курсовая\lesson_student.php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login_student.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Получаем данные урока, имя ученика и преподавателя
$stmt = $pdo->prepare("SELECT s.*, t.login AS teacher_login, u.login AS student_login 
    FROM schedule s 
    JOIN users t ON s.teacher_id = t.id 
    JOIN users u ON s.student_id = u.id
    WHERE s.id = ? AND s.student_id = ?");
$stmt->execute([$lesson_id, $student_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    echo "Урок не найден или доступ запрещён.";
    exit;
}

// Получаем оценку и дз
$stmt = $pdo->prepare("SELECT mark FROM marks WHERE student_id = ? AND subject = ? AND lesson_id = ?");
$stmt->execute([$student_id, $lesson['subject'], $lesson_id]);
$mark = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT task FROM homework WHERE student_id = ? AND subject = ? AND lesson_id = ?");
$stmt->execute([$student_id, $lesson['subject'], $lesson_id]);
$homework = $stmt->fetchColumn();

// Получаем отзыв, если уже есть
$stmt = $pdo->prepare("SELECT teacher_mark, wish FROM lesson_feedback WHERE lesson_id = ? AND student_id = ?");
$stmt->execute([$lesson_id, $student_id]);
$feedback = $stmt->fetch();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_mark = isset($_POST['teacher_mark']) ? intval($_POST['teacher_mark']) : null;
    $wish = trim($_POST['wish']);
    if ($teacher_mark < 1 || $teacher_mark > 10) $teacher_mark = null;
    if ($feedback) {
        $stmt = $pdo->prepare("UPDATE lesson_feedback SET teacher_mark = ?, wish = ? WHERE lesson_id = ? AND student_id = ?");
        $stmt->execute([$teacher_mark, $wish, $lesson_id, $student_id]);
        $msg = "Ваш отзыв обновлён!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO lesson_feedback (lesson_id, student_id, teacher_mark, wish) VALUES (?, ?, ?, ?)");
        $stmt->execute([$lesson_id, $student_id, $teacher_mark, $wish]);
        $msg = "Спасибо за ваш отзыв!";
    }
    // Обновить данные для отображения
    $feedback = ['teacher_mark' => $teacher_mark, 'wish' => $wish];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Урок</title>
    <link rel="stylesheet" href="lesson.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <main class="lesson-main">
        <h2>Урок</h2>
        <div class="lesson-info">
            <b>Ученик:</b> <?= htmlspecialchars($lesson['student_login']) ?><br>
            <b>Преподаватель:</b> <?= htmlspecialchars($lesson['teacher_login']) ?><br>
            <b>Дата:</b> <?= htmlspecialchars(date('d.m.Y', strtotime($lesson['lesson_date']))) ?><br>
            <b>Время:</b> <?= htmlspecialchars(substr($lesson['lesson_time'],0,5)) ?><br>
            <b>Предмет:</b> <?= htmlspecialchars($lesson['subject']) ?><br>
            <b>Комментарий:</b> <?= htmlspecialchars($lesson['comment']) ?><br>
            <b>Домашнее задание:</b> <?= htmlspecialchars($homework ?: 'Нет') ?><br>
            <b>Оценка:</b>
            <?php if ($mark): ?>
                <?= htmlspecialchars($mark) ?>
            <?php else: ?>
                <span style="color:#d32f2f;">Преподаватель ещё не оценил вас</span>
            <?php endif; ?>
        </div>
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
        <form method="post" class="lesson-form">
            <label for="teacher_mark">Оценить преподавателя (1-10):</label>
            <input type="number" min="1" max="10" name="teacher_mark" id="teacher_mark" value="<?= htmlspecialchars($feedback['teacher_mark'] ?? '') ?>" required>
            <label for="wish">Пожелания к следующему уроку:</label>
            <input type="text" name="wish" id="wish" maxlength="255" value="<?= htmlspecialchars($feedback['wish'] ?? '') ?>">
            <button type="submit">Отправить</button>
        </form>
        <form action="student_dashboard.php" method="get" style="margin-top:24px;">
            <button type="submit" class="back-btn">← К расписанию</button>
        </form>
    </main>
</body>
</html>