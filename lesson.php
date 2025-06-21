<?php
// filepath: c:\xampp\htdocs\Курсовая\lesson.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Получаем данные урока, имя ученика и преподавателя
$stmt = $pdo->prepare("SELECT s.*, t.login AS teacher_login, u.login AS student_login, u.email AS student_email 
    FROM schedule s 
    JOIN users t ON s.teacher_id = t.id 
    JOIN users u ON s.student_id = u.id
    WHERE s.id = ? AND s.teacher_id = ?");
$stmt->execute([$lesson_id, $_SESSION['user_id']]);
$lesson = $stmt->fetch();

if (!$lesson) {
    echo "Урок не найден или доступ запрещён.";
    exit;
}

// Обработка формы оценки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark'])) {
    $mark = intval($_POST['mark']);
    $stmt = $pdo->prepare("SELECT id FROM marks WHERE student_id = ? AND subject = ? AND lesson_id = ?");
    $stmt->execute([$lesson['student_id'], $lesson['subject'], $lesson_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE marks SET mark = ? WHERE student_id = ? AND subject = ? AND lesson_id = ?");
        $stmt->execute([$mark, $lesson['student_id'], $lesson['subject'], $lesson_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO marks (student_id, subject, mark, lesson_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$lesson['student_id'], $lesson['subject'], $mark, $lesson_id]);
    }
    $msg = "Оценка сохранена!";
}

// Обработка формы домашнего задания
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['homework'])) {
    $task = trim($_POST['homework']);
    $stmt = $pdo->prepare("SELECT id FROM homework WHERE student_id = ? AND subject = ? AND lesson_id = ?");
    $stmt->execute([$lesson['student_id'], $lesson['subject'], $lesson_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE homework SET task = ? WHERE student_id = ? AND subject = ? AND lesson_id = ?");
        $stmt->execute([$task, $lesson['student_id'], $lesson['subject'], $lesson_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO homework (student_id, subject, task, lesson_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$lesson['student_id'], $lesson['subject'], $task, $lesson_id]);
    }
    $msg = "Домашнее задание сохранено!";
}

// Получаем текущую оценку и дз
$stmt = $pdo->prepare("SELECT mark FROM marks WHERE student_id = ? AND subject = ? AND lesson_id = ?");
$stmt->execute([$lesson['student_id'], $lesson['subject'], $lesson_id]);
$mark = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT task FROM homework WHERE student_id = ? AND subject = ? AND lesson_id = ?");
$stmt->execute([$lesson['student_id'], $lesson['subject'], $lesson_id]);
$homework = $stmt->fetchColumn();

// Получаем отзыв и пожелание от ученика
$stmt = $pdo->prepare("SELECT teacher_mark, wish FROM lesson_feedback WHERE lesson_id = ? AND student_id = ?");
$stmt->execute([$lesson_id, $lesson['student_id']]);
$feedback = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Урок ученика <?= htmlspecialchars($lesson['student_login']) ?></title>
    <link rel="stylesheet" href="lesson.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <main class="lesson-main">
        <h2>Урок ученика</h2>
        <div class="lesson-info">
            <b>Ученик:</b> <?= htmlspecialchars($lesson['student_login']) ?><br>
            <b>Email:</b> <?= htmlspecialchars($lesson['student_email']) ?><br>
            <b>Преподаватель:</b> <?= htmlspecialchars($lesson['teacher_login']) ?><br>
            <b>Дата:</b> <?= htmlspecialchars(date('d.m.Y', strtotime($lesson['lesson_date']))) ?><br>
            <b>Время:</b> <?= htmlspecialchars(substr($lesson['lesson_time'],0,5)) ?><br>
            <b>Предмет:</b> <?= htmlspecialchars($lesson['subject']) ?><br>
            <b>Комментарий:</b> <?= htmlspecialchars($lesson['comment']) ?><br>
            <b>Домашнее задание:</b> <?= htmlspecialchars($homework ?: 'Нет') ?><br>
            <b>Оценка:</b> <?= $mark ? htmlspecialchars($mark) : '<span style="color:#d32f2f;">Нет</span>' ?><br>
            <?php if ($feedback): ?>
                <b>Оценка преподавателя учеником:</b> <?= htmlspecialchars($feedback['teacher_mark']) ?><br>
                <b>Пожелание ученика:</b> <?= htmlspecialchars($feedback['wish']) ?><br>
            <?php endif; ?>
        </div>
        <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>
        <form method="post" class="lesson-form" style="margin-bottom:18px;">
            <label for="mark">Оценка:</label>
            <input type="number" min="1" max="5" name="mark" id="mark" value="<?= htmlspecialchars($mark) ?>" required style="width:60px;">
            <button type="submit">Сохранить</button>
        </form>
        <form method="post" class="lesson-form">
            <label for="homework">Домашнее задание:</label>
            <input type="text" name="homework" id="homework" value="<?= htmlspecialchars($homework) ?>" style="width:70%;">
            <button type="submit">Сохранить</button>
        </form>
        <form action="teacher_dashboard.php" method="get" style="margin-top:24px;">
            <button type="submit" class="back-btn">← К расписанию</button>
        </form>
    </main>
</body>
</html>