<?php

require 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verify_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?")
            ->execute([$user['id']]);
        echo "Email подтвержден! Теперь вы можете <a href='login.php'>войти</a>.";
    } else {
        echo "Неверный токен подтверждения.";
    }
} else {
    echo "Токен не найден.";
}
?>