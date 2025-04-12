<?php
include 'data.php';
session_start();

// التحقق من إذا كان الطالب قد سجل الدخول
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'طالب') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

// جلب البيانات الخاصة بالطالب (المهام، المشروع)
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE assigned_user_id = ?");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

// جلب البيانات الخاصة بالمشروع الذي ينتمي إليه الطالب
$stmt_project = $pdo->prepare("SELECT * FROM projects WHERE team_id IN (SELECT team_id FROM team_members WHERE user_id = ?)");
$stmt_project->execute([$user_id]);
$project = $stmt_project->fetch();

// التحقق من وجود المهام والمشروع
if (!$tasks) {
    $tasks_error = "لا توجد مهام مسندة إليك.";
}

if (!$project) {
    $project_error = "لا يوجد مشروع مرتبط بهذا الطالب.";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الطالب</title>
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

        header {
            background-color: #007bff;
            color: white;
            padding: 15px 0;
        }

        header h2 {
            margin: 0;
            font-size: 24px;
        }

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

<header>
    <h2>مرحبًا، <?php echo $_SESSION['user']['full_name']; ?>!</h2>
</header>

<h3>مشروعك: <?php echo $project ? $project['title'] : $project_error; ?></h3>
<p><strong>الوصف:</strong> <?php echo $project ? $project['description'] : $project_error; ?></p>

<h3>المهام المسندة إليك:</h3>
<?php if (isset($tasks_error)) { echo "<p>$tasks_error</p>"; } ?>
<table>
    <tr>
        <th>العنوان</th>
        <th>الوصف</th>
        <th>التاريخ</th>
        <th>حالة التنفيذ</th>
    </tr>
    <?php if ($tasks): ?>
        <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?php echo $task['title']; ?></td>
            <td><?php echo $task['description']; ?></td>
            <td><?php echo $task['start_date']; ?> إلى <?php echo $task['end_date']; ?></td>
            <td><?php echo $task['status']; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<a class="logout-link" href="../logout.php">تسجيل الخروج</a>

</body>
</html>
