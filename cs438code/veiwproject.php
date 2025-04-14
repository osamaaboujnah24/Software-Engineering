<?php
include 'data.php';
session_start();

// التحقق من إذا كان المستخدم هو مدير مشروع
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

// التحقق من وجود project_id في الرابط
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    // تنفيذ استعلام للحصول على تفاصيل المشروع
    $stmt_project = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
    $stmt_project->execute([$project_id]);
    $project = $stmt_project->fetch();

    // التحقق إذا كان المشروع موجود
    if (!$project) {
        echo "المشروع غير موجود!";
        exit;
    }

    // جلب التقدم الخاص بالمشروع
    $stmt_progress = $pdo->prepare("SELECT progress_percent FROM progress_board WHERE project_id = ?");
    $stmt_progress->execute([$project_id]);
    $progress = $stmt_progress->fetch();
    $progress_percent = $progress ? $progress['progress_percent'] : 0;
} else {
    echo "المشروع غير موجود!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل المشروع</title>
    <style>
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

        .project-details {
            background-color: white;
            width: 60%;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .project-details p {
            font-size: 18px;
            margin: 10px 0;
        }

        .project-details h3 {
            margin-top: 20px;
            font-size: 22px;
            color: #007bff;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-link:hover {
            background-color: #0056b3;
        }

        .edit-delete-links a {
            padding: 10px 20px;
            background-color: #ffc107;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin: 5px;
        }

        .edit-delete-links a:hover {
            background-color: #e0a800;
        }

        .delete-link {
            background-color: #dc3545;
        }

        .delete-link:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>

<header>
    <h2>تفاصيل المشروع</h2>
</header>

<div class="project-details">
    <h3>اسم المشروع: <?php echo htmlspecialchars($project['title']); ?></h3>
    <p><strong>الوصف:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
    <p><strong>تاريخ البداية:</strong> <?php echo htmlspecialchars($project['start_date']); ?></p>
    <p><strong>تاريخ النهاية:</strong> <?php echo htmlspecialchars($project['end_date']); ?></p>
    <p><strong>التقدم:</strong> <?php echo $progress_percent; ?>%</p>
    <p><strong>المشرف:</strong> <?php echo htmlspecialchars($project['manager_id']); ?></p>
    <p><strong>الفريق:</strong> <?php echo htmlspecialchars($project['team_id']); ?></p>
    
    <div class="edit-delete-links">
        <a href="edit.php?id=<?php echo $project['project_id']; ?>">تعديل المشروع</a>
        <a href="delete_project.php?id=<?php echo $project['project_id']; ?>" class="delete-link" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا المشروع؟')">حذف المشروع</a>
    </div>

    <a href="dashboardadm.php" class="back-link">العودة إلى لوحة التحكم</a>
</div>

</body>
</html>
