<?php
session_start();
require 'db.php';

$teacher_code = 'SECRET2024'; // ваш секретный код
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $email = trim($_POST['email']);
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if ($code !== $teacher_code) {
        $msg = "Неверный код преподавателя!";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR login = ?");
        $stmt->execute([$email, $login]);
        if ($stmt->fetch()) {
            $msg = "Пользователь с таким email или логином уже существует!";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, login, password, role) VALUES (?, ?, ?, 'teacher')");
            $stmt->execute([$email, $login, $passwordHash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['login'] = $login;
            $_SESSION['role'] = 'teacher';
            header("Location: teacher_dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация преподавателя</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="register-container">
        <h2>Регистрация преподавателя</h2>
        <form method="post" autocomplete="off">
            <input type="text" name="code" placeholder="Код преподавателя" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Зарегистрироваться</button>

        </form>
        
        <form action="index.php" method="get">
            <button type="submit" class="back-btn">← На главную</button>
        </form>
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
    </div>
</body>
</html>