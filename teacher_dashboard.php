<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$msg = '';

// Создание класса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $class_name = trim($_POST['class_name']);
    if ($class_name) {
        $stmt = $pdo->prepare("INSERT INTO classes (name, teacher_id) VALUES (?, ?)");
        $stmt->execute([$class_name, $teacher_id]);
        $msg = "Класс создан!";
    }
}

// Добавление ученика
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_login = trim($_POST['student_login']);
    $student_password = $_POST['student_password'];
    $class_id = $_POST['class_id'];
    if ($student_login && $student_password && $class_id) {
        $stmt = $pdo->prepare("INSERT INTO users (login, password, role, class_id) VALUES (?, ?, 'student', ?)");
        $stmt->execute([$student_login, password_hash($student_password, PASSWORD_DEFAULT), $class_id]);
        $msg = "Ученик добавлен!";
    }
}

// Добавление урока
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lesson'])) {
    $student_id = $_POST['student_id'];
    $lesson_date = $_POST['lesson_date'];
    $lesson_time = $_POST['lesson_time'];
    $subject = trim($_POST['subject']);
    $comment = trim($_POST['comment']);
    if ($student_id && $lesson_date && $lesson_time && $subject) {
        $stmt = $pdo->prepare("INSERT INTO schedule (student_id, teacher_id, lesson_date, lesson_time, subject, comment) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $teacher_id, $lesson_date, $lesson_time, $subject, $comment]);
        $msg = "Урок назначен!";
    }
}

// Получение классов преподавателя
$stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$classes = $stmt->fetchAll();

// Получение учеников по классам
$class_students = [];
foreach ($classes as $class) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE class_id = ? AND role = 'student'");
    $stmt->execute([$class['id']]);
    $class_students[$class['id']] = $stmt->fetchAll();
}

// Удаление урока
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_lesson_id'])) {
    $delete_id = intval($_POST['delete_lesson_id']);
    $stmt = $pdo->prepare("DELETE FROM schedule WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$delete_id, $teacher_id]);
    $msg = "Урок отменён!";
}


// Удаление ученика и всех его данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student_id'])) {
    $student_id = intval($_POST['delete_student_id']);
    // Удаляем домашки, расписание, заявки (если есть), самого ученика
    $pdo->prepare("DELETE FROM homework WHERE student_id = ?")->execute([$student_id]);
    $pdo->prepare("DELETE FROM schedule WHERE student_id = ?")->execute([$student_id]);
    $pdo->prepare("DELETE FROM requests WHERE fio = (SELECT login FROM users WHERE id = ?)")->execute([$student_id]); // если заявки связаны по ФИО
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$student_id]);
    $msg = "Ученик и все его данные удалены!";
}

// --- Неделя с понедельника по воскресенье и навигация ---
$week_offset = isset($_GET['week']) ? intval($_GET['week']) : 0;
$today = new DateTime();
$today->modify(($week_offset * 7) . ' days');
$dayOfWeek = (int)$today->format('N');
$monday = clone $today;
$monday->modify('-' . ($dayOfWeek - 1) . ' days');

$week = [];
for ($i = 0; $i < 7; $i++) {
    $date = clone $monday;
    $date->modify("+$i days");
    $week[$date->format('Y-m-d')] = [
        'label' => $date->format('d.m.Y') . ' (' . ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'][$i] . ')',
        'lessons' => []
    ];
}
$stmt = $pdo->prepare("SELECT s.*, u.login AS student_login FROM schedule s JOIN users u ON s.student_id = u.id WHERE s.teacher_id = ? AND s.lesson_date >= ? AND s.lesson_date <= ? ORDER BY s.lesson_date, s.lesson_time");
$stmt->execute([
    $teacher_id,
    $monday->format('Y-m-d'),
    $date->format('Y-m-d')
]);
while ($row = $stmt->fetch()) {
    $week[$row['lesson_date']]['lessons'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Кабинет преподавателя</title>
    <link rel="stylesheet" href="teacher_dashboard.css">
</head>
<body>
    <header class="diary-header">
        <div class="logo">Кабинет преподавателя</div>
        <div class="profile-btn">
            <a href="teacher_requests.php" class="main-action-btn">Заявки</a>
            <a href="teacher_homework.php" class="main-action-btn">Домашние задания</a>
            <a href="profile.php">Профиль</a>

        </div>
    </header>
    <main>
        <div style="display: flex; gap: 16px; justify-content: center; margin-bottom: 24px;">
            <button id="openCreateClass" class="main-action-btn">Создать класс</button>
            <button id="openAddStudent" class="main-action-btn">Добавить ученика</button>
            <a href="student_wishes.php" class="main-action-btn" style="margin-bottom:18px;">Пожелания учеников</a>
        </div>

        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>

        <h2>Ваши классы и ученики</h2>
        <?php foreach ($classes as $class): ?>
            <section class="class-section">
                <h3><?= htmlspecialchars($class['name']) ?></h3>
                <?php if (!empty($class_students[$class['id']])): ?>

                    <ul>
                    <?php foreach ($class_students[$class['id']] as $student): ?>
                        <li style="display:flex;align-items:center;gap:10px;">
                            <?= htmlspecialchars($student['login']) ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Удалить этого ученика и все его данные?');">
                                <input type="hidden" name="delete_student_id" value="<?= $student['id'] ?>">
                                <button type="submit" class="delete-btn">Удалить</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Нет учеников в этом классе.</p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>

        <h2>Расписание на неделю</h2>

        <div class="schedule-week-nav" style="display:flex;justify-content:center;gap:16px;margin-bottom:18px;">
            <form method="get" style="display:inline;">
                <input type="hidden" name="week" value="<?= $week_offset - 1 ?>">
                <button type="submit">&larr; Прошлая неделя</button>
            </form>
            <form method="get" style="display:inline;">
                <input type="hidden" name="week" value="0">
                <button type="submit" class="main-action-btn" style="margin:0 8px;">Текущая неделя</button>
            </form>
            <form method="get" style="display:inline;">
                <input type="hidden" name="week" value="<?= $week_offset + 1 ?>">
                <button type="submit">Следующая неделя &rarr;</button>
            </form>
        </div>

        <div style="display:flex;justify-content:center;margin-bottom:30px; margin-left: -20px;">
            <button class="main-action-btn" onclick="openLessonModal()">Назначить урок</button>
        </div>
        <div class="schedule-week">
            <?php foreach ($week as $date => $info): ?>
                <div class="schedule-day">
                    <strong><?= $info['label'] ?></strong>
                    <?php if (!empty($info['lessons'])): ?>
                        <ul>
                            <?php foreach ($info['lessons'] as $lesson): ?>
                               
                                <li>
                                    <div class="lesson-header-row">
                                        <span class="lesson-time"><?= htmlspecialchars(substr($lesson['lesson_time'],0,5)) ?></span>
                                        <span class="lesson-subject"><?= htmlspecialchars($lesson['subject']) ?></span>
                                        <span class="lesson-student">(<?= htmlspecialchars($lesson['student_login']) ?>)</span>
                                    </div>
                                    <?php if ($lesson['comment']): ?>
                                        <div class="lesson-comment"><?= htmlspecialchars($lesson['comment']) ?></div>
                                    <?php endif; ?>
                                    <a href="lesson.php?id=<?= $lesson['id'] ?>" class="lesson-link-btn">К уроку</a>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="delete_lesson_id" value="<?= $lesson['id'] ?>">
                                        <button type="submit" onclick="return confirm('Отменить этот урок?');">Отменить</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div style="color:#aaa; margin-top:6px;">Нет уроков</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>


        <!-- Модальное окно создания класса -->
        <div id="modalCreateClass" class="modal-bg" style="display:none;">
            <div class="modal-window">
                <form method="post" class="create-class-form">
                    <h3>Создать класс</h3>
                    <input type="text" name="class_name" placeholder="Название класса" required>
                    <button type="submit" name="create_class">Создать</button>
                    <button type="button" class="close-modal">Отмена</button>
                </form>
            </div>
        </div>
        <!-- Модальное окно добавления ученика -->
        <div id="modalAddStudent" class="modal-bg" style="display:none;">
            <div class="modal-window">
                <form method="post" class="add-student-form">
                    <h3>Добавить ученика</h3>
                    <input type="text" name="student_login" placeholder="Логин ученика" required>
                    <input type="password" name="student_password" placeholder="Пароль ученика" required>
                    <select name="class_id" required>
                        <option value="">Выберите класс</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="add_student">Добавить</button>
                    <button type="button" class="close-modal">Отмена</button>
                </form>
            </div>
        </div>

                <!-- Модальное окно назначения урока -->
        <div id="lessonModal" class="modal-bg" style="display:none;">
          <div class="modal-window">
            <form method="post" class="add-lesson-form" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: center; margin-bottom: 0;">
                <h3 style="width:100%;text-align:center;color:#4e54c8;margin:0 0 18px 0;">Назначить урок</h3>
                <select name="student_id" required style="min-width: 180px;">
                    <option value="">Выберите ученика</option>
                    <?php foreach ($classes as $class): ?>
                        <?php foreach ($class_students[$class['id']] as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= htmlspecialchars($student['login']) ?> (<?= htmlspecialchars($class['name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="lesson_date" required style="min-width: 140px;">
                <input type="time" name="lesson_time" required style="min-width: 120px;">
                <input type="text" name="subject" placeholder="Предмет" required style="min-width: 140px;">
                <input type="text" name="comment" placeholder="Комментарий (необязательно)" style="min-width: 160px;">
                <div style="width:100%;display:flex;gap:14px;justify-content:center;margin-top:10px;">
                    <button type="submit" name="add_lesson" class="main-action-btn">Назначить урок</button>
                    <button type="button" class="main-action-btn" style="background:#eee;color:#4e54c8;" onclick="closeLessonModal()">Отмена</button>
                </div>
            </form>
          </div>
        </div>
    </main>
    <script src="teacher_dashboard.js"></script>
</body>
</html>