<?php
include 'data.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // غير مشفر حسب طلبك
    $role = 'طالب';

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status, permission_level)
                           VALUES (?, ?, ?, ?, 'نشط', 'عرض')");
    $stmt->execute([$name, $email, $password, $role]);

    $_SESSION['user'] = ['full_name' => $name, 'role' => $role];
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل طالب جديد</title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background-color: #f0f2f5;
            padding: 40px;
            text-align: center;
        }

        form {
            background-color: white;
            width: 400px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h3 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            width: 95%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-link {
            margin-top: 20px;
            display: block;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <form method="POST">
        <h3>تسجيل طالب جديد</h3>
        <input type="text" name="full_name" placeholder="الاسم الكامل" required><br>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required><br>
        <input type="password" name="password" placeholder="كلمة المرور" required><br>
        <button type="submit">تسجيل</button>
        <a class="back-link" href="login.php">هل لديك حساب؟ تسجيل الدخول</a>
    </form>

</body>
</html>
