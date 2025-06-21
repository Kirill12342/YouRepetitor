<?php


session_start();
require 'db.php';

$user_id = $_SESSION['user_id'];
$msg = '';

// Получаем текущие данные пользователя
$stmt = $pdo->prepare("SELECT email, login FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Обработка изменения профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $new_email = trim($_POST['email']);
    $new_login = trim($_POST['login']);
    $current_password = $_POST['current_password'];

    // Проверяем текущий пароль
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if ($row && password_verify($current_password, $row['password'])) {
        // Проверка на уникальность email и логина (кроме текущего пользователя)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR login = ?) AND id != ?");
        $stmt->execute([$new_email, $new_login, $user_id]);
        if ($stmt->fetch()) {
            $msg = "Email или логин уже занят другим пользователем!";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, login = ? WHERE id = ?");
            $stmt->execute([$new_email, $new_login, $user_id]);
            $_SESSION['login'] = $new_login;
            $msg = "Данные успешно обновлены!";
            // Обновляем данные для отображения
            $user['email'] = $new_email;
            $user['login'] = $new_login;
        }
    } else {
        $msg = "Неверный текущий пароль!";
    }
}

// Выход из аккаунта
if (isset($_POST['logout'])) {
    $role = $_SESSION['role'] ?? '';
    session_destroy();
    if ($role === 'teacher') {
        header("Location: login_teacher.php");
    } else {
        header("Location: login_student.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-header">
        <a href="<?php
            if ($_SESSION['role'] === 'teacher') {
                echo 'teacher_dashboard.php';
            } else {
                echo 'student_dashboard.php';
            }
        ?>">← Назад в дневник</a>
    </div>
    <main>
        <h2>Профиль пользователя</h2>
        <form method="post" autocomplete="off" class="profile-form">
            <label for="email">Почта</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <label for="login">Логин</label>
            <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" required>
            <label for="current_password">Текущий пароль для подтверждения</label>
            <input type="password" id="current_password" name="current_password" required>
            <button type="submit" name="update">Сохранить изменения</button>
        </form>
        <form method="post">
            <button type="submit" name="logout" class="logout-btn">Выйти из аккаунта</button>
        </form>
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
        <form action="<?php
            if ($_SESSION['role'] === 'teacher') {
                echo 'teacher_dashboard.php';
            } else {
                echo 'student_dashboard.php';
            }
        ?>" method="get">
            <button class="back-btn" type="submit">← В дневник</button>
        </form>
    </main>
</body>
</html>