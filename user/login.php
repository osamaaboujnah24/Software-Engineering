<?php
include 'LoginHandler.php';

$login = new LoginHandler($pdo);
$login->processLogin();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: #fff;
            width: 380px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: all 0.3s ease;
        }

        form:hover {
            transform: scale(1.03);
        }

        h3 {
            margin-bottom: 20px;
            font-size: 26px;
            color: #2ecc71;
        }

        input {
            width: 90%;
            padding: 12px;
            margin: 12px 0;
            font-size: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #2ecc71;
            outline: none;
            box-shadow: 0 0 8px rgba(46, 204, 113, 0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #2ecc71;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #27ae60;
        }

        .error {
            color: #e74c3c;
            margin-top: 15px;
            font-size: 14px;
        }

        .back-link {
            margin-top: 20px;
            display: inline-block;
            color: #2ecc71;
            text-decoration: none;
            font-size: 15px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<form method="POST">
    <h3>تسجيل الدخول</h3>

    <?php if ($login->getError()) echo "<p class='error'>" . $login->getError() . "</p>"; ?>

    <input type="email" name="email" placeholder="البريد الإلكتروني" required><br>
    <input type="password" name="password" placeholder="كلمة المرور" required><br>
    <button type="submit">دخول</button>

    <a class="back-link" href="register.php">إنشاء حساب طالب جديد</a>
</form>

</body>
</html>
