<?php
session_start();
require 'db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (login = ? OR email = ?) AND role = 'student'");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['role'] = 'student';
        header("Location: student_dashboard.php");
        exit;
    } else {
        $msg = "Неверный логин или пароль!";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход для ученика</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Вход для ученика</h2>
        <form method="post" autocomplete="off">
            <input type="text" name="login" placeholder="Логин или Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
            
            
        </form>
        
        <form action="index.php" method="get">
            <button type="submit" class="back-btn">← На главную</button>
        </form>
        
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
    </div>
</body>
</html>