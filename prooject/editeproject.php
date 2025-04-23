<?php
include 'database.php';
session_start();

class ProjectEditor {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getProjectById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            die("خطأ في جلب بيانات المشروع: " . $e->getMessage());
        }
    }

    public function updateProject($title, $description, $start_date, $end_date, $manager_id, $team_id, $project_id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE projects SET title = ?, description = ?, start_date = ?, end_date = ?, manager_id = ?, team_id = ? WHERE project_id = ?");
            $stmt->execute([$title, $description, $start_date, $end_date, $manager_id, $team_id, $project_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die("خطأ أثناء تحديث المشروع: " . $e->getMessage());
        }
    }

    public function getManagers() {
        $stmt = $this->pdo->prepare("SELECT * FROM project_managers");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTeams() {
        $stmt = $this->pdo->prepare("SELECT * FROM teams");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

$editor = new ProjectEditor($pdo);

if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $project = $editor->getProjectById($project_id);

    if (!$project) {
        die("المشروع غير موجود!");
    }
} else {
    die("المشروع غير موجود!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['title'], $_POST['description'], $_POST['start_date'], $_POST['end_date'], $_POST['manager_id'], $_POST['team_id'])) {
        $updated = $editor->updateProject(
            $_POST['title'],
            $_POST['description'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['manager_id'],
            $_POST['team_id'],
            $project_id
        );

        if ($updated) {
            header("Location: dashboardadm.php");
            exit;
        } else {
            echo "لم يتم تعديل المشروع.";
        }
    } else {
        echo "يرجى ملء جميع الحقول.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل المشروع</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #2c3e50;
            color: #ecf0f1;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        header {
            background-color: #16a085;
            color: white;
            padding: 30px 0;
        }
        header h2 {
            font-size: 32px;
            margin: 0;
        }
        form {
            background-color: #34495e;
            width: 70%;
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }
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
            color: #e74c3c;
        }
        input, select, textarea {
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        button {
            padding: 16px 32px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<header>
    <h2>تعديل المشروع</h2>
</header>

<form method="POST">
    <label>اسم المشروع:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>

    <label>الوصف:</label>
    <textarea name="description" required><?php echo htmlspecialchars($project['description']); ?></textarea>

    <label>تاريخ البداية:</label>
    <input type="date" name="start_date" value="<?php echo htmlspecialchars($project['start_date']); ?>" required>

    <label>تاريخ النهاية:</label>
    <input type="date" name="end_date" value="<?php echo htmlspecialchars($project['end_date']); ?>" required>

    <label>المشرف:</label>
    <select name="manager_id" required>
        <option value="">اختر المشرف</option>
        <?php
        foreach ($editor->getManagers() as $manager) {
            $selected = ($manager['manager_id'] == $project['manager_id']) ? 'selected' : '';
            echo "<option value='{$manager['manager_id']}' $selected>" . htmlspecialchars($manager['name']) . "</option>";
        }
        ?>
    </select>

    <label>الفريق:</label>
    <select name="team_id" required>
        <option value="">اختر الفريق</option>
        <?php
        foreach ($editor->getTeams() as $team) {
            $selected = ($team['team_id'] == $project['team_id']) ? 'selected' : '';
            echo "<option value='{$team['team_id']}' $selected>" . htmlspecialchars($team['team_name']) . "</option>";
        }
        ?>
    </select>

    <button type="submit">تحديث المشروع</button>
</form>

</body>
</html>