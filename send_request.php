<?php
// filepath: c:\xampp\htdocs\Курсовая\send_request.php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fio = trim($_POST['fio']);
    $contacts = trim($_POST['contacts']);
    $services = trim($_POST['services']);
    $stmt = $pdo->prepare("INSERT INTO requests (fio, contacts, services, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$fio, $contacts, $services]);
}
header("Location: index.php?request=ok");
exit;