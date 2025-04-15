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
                header("Location: dashboardadm.php");
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
    <style>
        /* تصميم عام */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #2c3e50; /* خلفية داكنة */
            color: #ecf0f1; /* لون النص الفاتح */
            margin: 0;
            padding: 0;
            text-align: center;
        }

        /* ترويسة */
        header {
            background-color: #16a085; /* ترويسة بلون أخضر مائل للأزرق */
            color: white;
            padding: 30px 0;
            text-align: center;
            width: 100%;
        }

        header h2 {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }

        /* تصميم النموذج */
        form {
            background-color: #34495e; /* خلفية داكنة للنموذج */
            width: 70%;
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        /* تأثير عند تمرير الماوس */
        form:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            text-align: right;
            margin: 15px 0 8px;
            font-size: 18px;
            font-weight: 500;
            color: #e74c3c; /* لون أحمر للنصوص */
        }

        input[type="text"], input[type="date"], textarea, select {
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* تأثير التركيز */
        input[type="text"]:focus, input[type="date"]:focus, textarea:focus, select:focus {
            border-color: #2ecc71; /* أخضر عند التركيز */
            box-shadow: 0 0 8px rgba(46, 204, 113, 0.3);
            outline: none;
        }

        textarea {
            resize: vertical;
            height: 150px;
        }

        /* أزرار */
        button {
            padding: 16px 32px;
            background-color: #28a745; /* لون أخضر مائل للأزرق للأزرار */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 20px;
            width: 100%;
        }

        button:hover {
            background-color: #218838; /* تدرج اللون الأخضر عند التمرير */
            transform: translateY(-2px);
        }

        /* رابط تسجيل الخروج */
        .logout-link {
            margin-top: 30px;
            display: inline-block;
            padding: 12px 25px;
            background-color: #f39c12; /* لون دافئ وأنيق */
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .logout-link:hover {
            background-color: #e67e22; /* تدرج اللون عند التمرير */
            transform: translateY(-2px);
        }
    </style>
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
    <select name="manager_id" required>
        <option value="">اختر المشرف</option>
        <?php
        // جلب قائمة المشرفين من جدول project_managers
        $stmt_managers = $pdo->prepare("SELECT * FROM project_managers");
        $stmt_managers->execute();
        $managers = $stmt_managers->fetchAll();

        // عرض المشرفين في قائمة منسدلة
        foreach ($managers as $manager) {
            echo "<option value='" . $manager['manager_id'] . "' " . ($manager['manager_id'] == $project['manager_id'] ? 'selected' : '') . ">" . htmlspecialchars($manager['name']) . "</option>";
        }
        ?>
    </select><br>

    <label for="team_id">الفريق:</label>
    <select name="team_id" required>
        <option value="">اختر الفريق</option>
        <?php
        // جلب قائمة الفرق من جدول teams
        $stmt_teams = $pdo->prepare("SELECT * FROM teams");
        $stmt_teams->execute();
        $teams = $stmt_teams->fetchAll();

        // عرض الفرق في قائمة منسدلة
        foreach ($teams as $team) {
            echo "<option value='" . $team['team_id'] . "' " . ($team['team_id'] == $project['team_id'] ? 'selected' : '') . ">" . htmlspecialchars($team['team_name']) . "</option>";
        }
        ?>
    </select><br>

    <button type="submit">تحديث المشروع</button>
</form>

</body>
</html>
