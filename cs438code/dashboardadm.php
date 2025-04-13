<?php
include 'data.php';
session_start();

// التحقق من إذا كان المستخدم هو مدير مشروع
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

// جلب المشاريع
$stmt_projects = $pdo->prepare("SELECT * FROM projects");
$stmt_projects->execute();
$projects = $stmt_projects->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم مدير المشروع</title>
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

        h2, h3 {
            color: #007bff;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* شريط التنقل */
        nav {
            background-color: #007bff;
            padding: 10px 0;
        }

        nav a {
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
        }

        nav a:hover {
            background-color: #0056b3;
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

        input[type="text"], input[type="email"], input[type="password"], textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        /* جدول المشاريع */
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        /* الأزرار */
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

        /* رابط تسجيل الخروج */
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

<!-- شريط التنقل -->
<nav>
    <a href="dashboard.php">لوحة التحكم</a>
    <a href="add_project.php">إضافة مشروع جديد</a>
    <a href="logout.php">تسجيل الخروج</a>
</nav>

<header>
    <h2>مرحبًا، <?php echo $_SESSION['user']['full_name']; ?> (مدير مشروع)</h2>
</header>

<h3>المشاريع الحالية:</h3>
<table>
    <tr>
        <th>العنوان</th>
        <th>الوصف</th>
        <th>التاريخ</th>
        <th>التقدم (%)</th>
        <th>الإجراءات</th>
    </tr>
    <?php foreach ($projects as $project):
        // جلب التقدم لكل مشروع
        $stmt_progress = $pdo->prepare("SELECT progress_percent FROM progress_board WHERE project_id = ?");
        $stmt_progress->execute([$project['project_id']]);
        $progress = $stmt_progress->fetch();
        $progress_percent = $progress ? $progress['progress_percent'] : 0;
    ?>
    <tr>
        <td><?php echo $project['title']; ?></td>
        <td><?php echo $project['description']; ?></td>
        <td><?php echo $project['start_date']; ?> إلى <?php echo $project['end_date']; ?></td>
        <td><?php echo $progress_percent; ?>%</td>
        <td>
            <a href="edit_project.php?id=<?php echo $project['project_id']; ?>">تعديل</a> |
            <a href="delete_project.php?id=<?php echo $project['project_id']; ?>">حذف</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>


</body>
</html>
