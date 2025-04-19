<?php
include 'database.php';
session_start();

class ProjectCreator {
    private $pdo;
    public $managers = [];
    public $teams = [];
    public $error = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
            header("Location: login.php");
            exit;
        }

        $this->loadManagers();
        $this->loadTeams();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->handleFormSubmission();
        }
    }

    private function loadManagers() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM project_managers");
            $stmt->execute();
            $this->managers = $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->error = "فشل تحميل المشرفين: " . $e->getMessage();
        }
    }

    private function loadTeams() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM teams");
            $stmt->execute();
            $this->teams = $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->error = "فشل تحميل الفرق: " . $e->getMessage();
        }
    }

    private function handleFormSubmission() {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $manager_id = $_POST['manager_id'];
        $team_id = $_POST['team_id'];

        try {
            $stmt = $this->pdo->prepare("INSERT INTO projects (title, description, start_date, end_date, manager_id, team_id)
                                         VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $start_date, $end_date, $manager_id, $team_id]);

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
            color: #e74c3c;
        }

        input, textarea, select {
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            padding: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            width: 100%;
        }

        button:hover {
            background-color: #218838;
        }

        .error {
            color: #e74c3c;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<header>
    <h2>إنشاء مشروع جديد</h2>
</header>

<?php if (!empty($creator->error)): ?>
    <p class="error"><?php echo $creator->error; ?></p>
<?php endif; ?>

<form method="POST">
    <label for="title">اسم المشروع:</label>
    <input type="text" name="title" required>

    <label for="description">الوصف:</label>
    <textarea name="description" required></textarea>

    <label for="start_date">تاريخ البداية:</label>
    <input type="date" name="start_date" required>

    <label for="end_date">تاريخ النهاية:</label>
    <input type="date" name="end_date" required>

    <label for="manager_id">المشرف:</label>
    <select name="manager_id" required>
        <option value="">اختر المشرف</option>
        <?php foreach ($creator->managers as $manager): ?>
            <option value="<?php echo $manager['manager_id']; ?>"><?php echo htmlspecialchars($manager['name']); ?></option>
        <?php endforeach; ?>
    </select>

    <label for="team_id">الفريق:</label>
    <select name="team_id" required>
        <option value="">اختر الفريق</option>
        <?php foreach ($creator->teams as $team): ?>
            <option value="<?php echo $team['team_id']; ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">إنشاء المشروع</button>
</form>

</body>
</html>