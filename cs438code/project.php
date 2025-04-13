<?php
include 'database.php';
session_start();

// التحقق من إذا كان المستخدم هو مدير مشروع
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
    header("Location: cs438-1/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استلام البيانات من النموذج
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $manager_id = $_POST['manager_id']; // من المستخدمين
    $team_id = $_POST['team_id']; // الفريق المرتبط بالمشروع

    // إضافة المشروع إلى قاعدة البيانات
    $stmt = $pdo->prepare("INSERT INTO projects (title, description, start_date, end_date, manager_id, team_id) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $start_date, $end_date, $manager_id, $team_id]);

    // إعادة التوجيه إلى لوحة الأدمن بعد الإضافة
    header("Location: admin/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء مشروع جديد</title>
    <style>
        /* تصميم عام */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        h2 {
            color: #007bff;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* ترويسة */
        header {
            background-color: #007bff;
            color: white;
            padding: 15px 0;
        }

        header h2 {
            margin: 0;
            font-size: 24px;
        }

        /* تصميم النموذج */
        form {
            background-color: white;
            width: 60%;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            text-align: right;
            margin: 10px 0 5px;
            font-size: 16px;
        }

        input[type="text"], input[type="date"], textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            height: 120px;
        }

        button {
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .logout-link {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .logout-link:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<header>
    <h2>إنشاء مشروع جديد</h2>
</header>

<form method="POST">
    <label for="title">اسم المشروع:</label>
    <input type="text" name="title" required><br>

    <label for="description">الوصف:</label>
    <textarea name="description" required></textarea><br>

    <label for="start_date">تاريخ البداية:</label>
    <input type="date" name="start_date" required><br>

    <label for="end_date">تاريخ النهاية:</label>
    <input type="date" name="end_date" required><br>

    <label for="manager_id">المشرف:</label>
    <input type="text" name="manager_id" required><br>

    <label for="team_id">الفريق:</label>
    <input type="text" name="team_id" required><br>

    <button type="submit">إنشاء المشروع</button>
</form>

</body>
</html>
