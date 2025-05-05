<?php
include 'database.php';
session_start();

class ProjectCreator {
    private $pdo;
    public $managers = [];
    public $supervisors = [];
    public $teams = [];
    public $error = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
            header("Location: login.php");
            exit;
        }

        $this->loadManagers();
        $this->loadSupervisors();
        $this->loadTeams();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->handleFormSubmission();
        }
    }

    private function loadManagers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'مدير مشروع'");
        $stmt->execute();
        $this->managers = $stmt->fetchAll();
    }

    private function loadSupervisors() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'مشرف'");
        $stmt->execute();
        $this->supervisors = $stmt->fetchAll();
    }

    private function loadTeams() {
        $stmt = $this->pdo->prepare("SELECT * FROM teams");
        $stmt->execute();
        $this->teams = $stmt->fetchAll();
    }

    private function handleFormSubmission() {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $manager_id = $_POST['manager_id'];
        $supervisor_id = $_POST['supervisor_id'];
        $team_id = $_POST['team_id'];

        try {
            $stmt = $this->pdo->prepare("INSERT INTO projects 
                (title, description, start_date, end_date, manager_id, supervisor_id, team_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $start_date, $end_date, $manager_id, $supervisor_id, $team_id]);

            $project_id = $this->pdo->lastInsertId();

            $stmtManager = $this->pdo->prepare("INSERT INTO project_managers (project_id, manager_id) VALUES (?, ?)");
            $stmtManager->execute([$project_id, $manager_id]);

            header("Location: admin/dashboard.php");
            exit;
        } catch (PDOException $e) {
            $this->error = "فشل في إنشاء المشروع: " . $e->getMessage();
        }
    }
}

$creator = new ProjectCreator($pdo);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء مشروع جديد</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #2c3e50;
            color: #fff;
            text-align: center;
            padding: 0;
            margin: 0;
        }
        header {
            background-color: #16a085;
            padding: 30px 0;
        }
        header h2 {
            font-size: 30px;
            margin: 0;
        }
        form {
            background-color: #34495e;
            width: 70%;
            max-width: 700px;
            margin: 30px auto;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        label {
            display: block;
            margin-top: 15px;
            text-align: right;
            color: #f1c40f;
            font-size: 18px;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            padding: 14px;
            width: 100%;
            background-color: #27ae60;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: #219150;
        }
        .error {
            color: #e74c3c;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<header>
    <h2>إنشاء مشروع جديد</h2>
</header>

<?php if (!empty($creator->error)): ?>
    <p
class="error"><?php echo $creator->error; ?></p>
<?php endif; ?>

<form method="POST">
    <label for="title">اسم المشروع:</label>
    <input type="text" name="title" required>

    <label for="description">وصف المشروع:</label>
    <textarea name="description" required></textarea>

    <label for="start_date">تاريخ البداية:</label>
    <input type="date" name="start_date" required>

    <label for="end_date">تاريخ النهاية:</label>
    <input type="date" name="end_date" required>

    <label for="manager_id">مدير المشروع:</label>
    <select name="manager_id" required>
        <option value="">اختر مدير المشروع</option>
        <?php foreach ($creator->managers as $manager): ?>
            <option value="<?= $manager['user_id'] ?>"><?= htmlspecialchars($manager['full_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="supervisor_id">المشرف:</label>
    <select name="supervisor_id" required>
        <option value="">اختر المشرف</option>
        <?php foreach ($creator->supervisors as $supervisor): ?>
            <option value="<?= $supervisor['user_id'] ?>"><?= htmlspecialchars($supervisor['full_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="team_id">الفريق:</label>
    <select name="team_id" required>
        <option value="">اختر الفريق</option>
        <?php foreach ($creator->teams as $team): ?>
            <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">إنشاء المشروع</button>
</form>

</body>
</html>