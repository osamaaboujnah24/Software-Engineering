<?php
include 'data.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // البحث عن المستخدم في قاعدة البيانات
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // التحقق من صحة كلمة المرور
    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user;

        // التوجيه حسب الدور
        if ($user['role'] === 'مدير مشروع') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] === 'طالب') {
            header("Location: dashboard.php");
        } else {
            $error = "نوع المستخدم غير معروف.";
        }
        exit;
    } else {
        $error = "بيانات الدخول غير صحيحة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <style>
        body {
            font-family: Tahoma;
            background-color: #f9f9f9;
            padding: 40px;
            text-align: center;
        }

        form {
            background-color: white;
            width: 400px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h3 {
            margin-bottom: 20px;
        }

        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            width: 95%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .back-link {
            margin-top: 15px;
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
    <h3>تسجيل الدخول</h3>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <input type="email" name="email" placeholder="البريد الإلكتروني" required><br>
    <input type="password" name="password" placeholder="كلمة المرور" required><br>
    <button type="submit">دخول</button>

    <a class="back-link" href="register.php">إنشاء حساب طالب جديد</a>
</form>

</body>
</html>
