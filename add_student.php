<?php
// Только для авторизованных преподавателей!
if ($_SESSION['role'] !== 'teacher') exit('Доступ запрещён');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $class_id = $_POST['class_id'];

    // Регистрируем ученика (role = 'student')
    $stmt = $pdo->prepare("INSERT INTO users (login, password, role, class_id) VALUES (?, ?, 'student', ?)");
    $stmt->execute([$login, password_hash($password, PASSWORD_DEFAULT), $class_id]);
    $msg = "Ученик добавлен!";
}
?>
<form method="post">
    <input type="text" name="login" placeholder="Логин ученика" required>
    <input type="password" name="password" placeholder="Пароль ученика" required>
    <select name="class_id">
        <!-- Вывести список классов преподавателя -->
    </select>
    <button type="submit">Добавить ученика</button>
</form>