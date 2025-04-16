<?php
include 'data.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // غير مشفر حسب طلبك
    $role = 'طالب';

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status, permission_level)
                               VALUES (?, ?, ?, ?, 'نشط', 'عرض')");
        $stmt->execute([$name, $email, $password, $role]);

        $_SESSION['user'] = ['full_name' => $name, 'role' => $role];
        header("Location: dashboardST.php");
        exit;
    } catch (PDOException $e) {
        $error = "حدث خطأ أثناء التسجيل: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل طالب جديد</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #007bff, #00bcd4);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: white;
            width: 400px;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        h3 {
            margin-bottom: 20px;
            color: #007bff;
            font-size: 24px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-link {
            margin-top: 20px;
            display: block;
            color: #007bff;
            text-decoration: none;
            font-size: 15px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<form method="POST">
    <h3>تسجيل طالب جديد</h3>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <input type="text" name="full_name" placeholder="الاسم الكامل" required>
    <input type="email" name="email" placeholder="البريد الإلكتروني" required>
    <input type="password" name="password" placeholder="كلمة المرور" required>
    <button type="submit">تسجيل</button>
    <a class="back-link" href="login.php">هل لديك حساب؟ تسجيل الدخول</a>
</form>

</body>
</html>
