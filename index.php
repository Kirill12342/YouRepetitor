<?php

require 'db.php';
// Получаем количество учеников
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$students_count = $stmt->fetchColumn();



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="image/favicon.png">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>

</head>
<body>
    <div class="header">
        <div class="left-block_header">
            <span>Зарегистрировано учеников: <?php echo $students_count; ?></span>

            

        </div>
        <div class="center-block_header">
            <button class="main-action-btn" onclick="openRequestModal()">Оставить заявку</button>

            <button>Оставить заяку</button>
            <button>Оставить заяку</button>
            
        </div>
        <div class="right-block_header" id="authBtn">
            <a href="javascript:void(0);">
             <img src="./image/logo.png" alt="">
            </a>
            <p>Вход</p> 
        </div>

    </div>
    <div class="gradient-divider"></div>
        <!-- ...existing code... -->
        <div id="authModal">
            <div class="modal-content">
                <button id="closeModal">&times;</button>
                <h2>Выберите действие</h2>
                <button onclick="window.location.href='login_teacher.php'">Вход для преподавателя</button>
                <button onclick="window.location.href='login_student.php'">Вход для ученика</button>
                <button onclick="window.location.href='register_teacher.php'">Регистрация преподавателя</button>
            </div>
        </div>
        <!-- ...existing code... -->


        
        <div id="requestModal" class="modal-bg" style="display:none;">
          <div class="modal-window">
            <form method="post" action="send_request.php">
              <h3>Оставить заявку</h3>
                
              <label>ФИО:<br>
                <input type="text" name="fio" required>
              </label><br>
              <label>Контактные данные:<br>
                <input type="text" name="contacts" required>
              </label><br>
              <label>Требуемые услуги:<br>
                <textarea name="services" required></textarea>
              </label><br>
              <div style="display:flex;gap:12px;justify-content:center;">
                <button type="submit" class="main-action-btn">Отправить</button>
                <button type="button" class="main-action-btn" style="background:#eee;color:#4e54c8;" onclick="closeRequestModal()">Отмена</button>
              </div>
            </form>
          </div>
        </div>

    
 <script src="index.js"></script>
</body>
</html> 