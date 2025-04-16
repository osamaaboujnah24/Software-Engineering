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

    // جلب تفاصيل المشروع
    $stmt_project = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
    $stmt_project->execute([$project_id]);
    $project = $stmt_project->fetch();

    if (!$project) {
        echo "المشروع غير موجود!";
        exit;
    }

    // جلب التقدم
    $stmt_progress = $pdo->prepare("SELECT progress_percent FROM progress_board WHERE project_id = ?");
    $stmt_progress->execute([$project_id]);
    $progress = $stmt_progress->fetch();
    $progress_percent = $progress ? $progress['progress_percent'] : 0;

    // جلب التقارير المرتبطة بالمشروع
    $stmt_reports = $pdo->prepare("SELECT * FROM reports WHERE project_id = ?");
    $stmt_reports->execute([$project_id]);
    $reports = $stmt_reports->fetchAll();
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
            width: 70%;
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
            margin-top: 30px;
            font-size: 22px;
            color: #007bff;
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

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        tr:hover td {
            background-color: #f1f1f1;
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

    <h3>التقارير المرسلة:</h3>
    <?php if (!empty($reports)): ?>
        <table>
            <tr>
                <th>عنوان التقرير</th>
                <th>المحتوى</th>
                <th>تاريخ الإرسال</th>
            </tr>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td><?php echo htmlspecialchars($report['title']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($report['content'])); ?></td>
                <td><?php echo htmlspecialchars($report['created_at'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="color: #777;">لا توجد تقارير مرسلة لهذا المشروع بعد.</p>
    <?php endif; ?>

    <a href="dashboardadm.php" class="back-link">العودة إلى لوحة التحكم</a>
</div>


</body>
</html>
