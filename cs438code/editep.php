<?php
include 'database.php';
session_start();

// التحقق من إذا كان المستخدم هو مدير مشروع
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

// التحقق من وجود قيمة project_id في الرابط
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    // تنفيذ استعلام للحصول على تفاصيل المشروع
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(); // جلب البيانات من الاستعلام

    // التحقق إذا كان المشروع موجود
    if (!$project) {
        echo "المشروع غير موجود!";
        exit;
    }
} else {
    echo "المشروع غير موجود!";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // التحقق من وجود البيانات في الـ POST
    if (isset($_POST['title'], $_POST['description'], $_POST['start_date'], $_POST['end_date'], $_POST['manager_id'], $_POST['team_id'])) {
        
        // استلام البيانات من النموذج
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $manager_id = $_POST['manager_id'];
        $team_id = $_POST['team_id'];

        // إضافة سجل الأخطاء
        try {
            // تحديث المشروع في قاعدة البيانات
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, start_date = ?, end_date = ?, manager_id = ?, team_id = ? WHERE project_id = ?");
            $stmt->execute([$title, $description, $start_date, $end_date, $manager_id, $team_id, $project_id]);

            // التحقق من نجاح العملية
            if ($stmt->rowCount() > 0) {
                // إعادة التوجيه إلى لوحة الأدمن بعد التعديل
                header("Location: admin/dashboard.php");
                exit;
            } else {
                echo "لم يتم تعديل المشروع. تأكد من وجود بيانات جديدة لتحديثها.";
            }
        } catch (Exception $e) {
            echo "حدث خطأ أثناء التعديل: " . $e->getMessage();
        }
    } else {
        echo "الرجاء ملء جميع الحقول.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل المشروع</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h2>تعديل المشروع</h2>
</header>

<form method="POST">
    <label for="title">اسم المشروع:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>" required><br>

    <label for="description">الوصف:</label>
    <textarea name="description" required><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea><br>

    <label for="start_date">تاريخ البداية:</label>
    <input type="date" name="start_date" value="<?php echo htmlspecialchars($project['start_date'] ?? ''); ?>" required><br>

    <label for="end_date">تاريخ النهاية:</label>
    <input type="date" name="end_date" value="<?php echo htmlspecialchars($project['end_date'] ?? ''); ?>" required><br>

    <label for="manager_id">المشرف:</label>
    <input type="text" name="manager_id" value="<?php echo htmlspecialchars($project['manager_id'] ?? ''); ?>" required><br>

    <label for="team_id">الفريق:</label>
    <input type="text" name="team_id" value="<?php echo htmlspecialchars($project['team_id'] ?? ''); ?>" required><br>

    <button type="submit">تحديث المشروع</button>
</form>

</body>
</html>
