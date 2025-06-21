<?php
// filepath: c:\xampp\htdocs\Курсовая\teacher_requests.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login_teacher.php");
    exit;
}

$requests = $pdo->query("SELECT * FROM requests ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заявки</title>
    <link rel="stylesheet" href="teacher_requests.css">
</head>
<body>
    <div class="profile-btn" style="text-align:right; margin: 18px 24px 0 0;">
        <a href="teacher_dashboard.php" class="main-action-btn">← В кабинет</a>
    </div>
    <main>
        <h2>Заявки пользователей</h2>
        <div class="requests-list">
            <?php foreach ($requests as $req): ?>
            <div class="request-card">
                <div><b>ФИО:</b> <?= htmlspecialchars($req['fio']) ?></div>
                <div><b>Контакты:</b> <?= htmlspecialchars($req['contacts']) ?></div>
                <div><b>Услуги:</b> <?= nl2br(htmlspecialchars($req['services'])) ?></div>
                <div class="request-date"><?= htmlspecialchars($req['created_at']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($requests)): ?>
                <div style="color:#888;text-align:center;">Нет заявок</div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>