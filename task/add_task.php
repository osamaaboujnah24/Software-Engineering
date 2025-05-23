<?php
include 'data.php';
session_start();

class TaskManager {
    private $pdo;
    public $users = [];
    public $projects = [];
    public $error = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
            header("Location: login.php");
            exit;
        }

        $this->loadUsers();
        $this->loadProjects();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleTaskCreation();
        }
    }

    private function loadUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role != 'مدير مشروع'");
        $stmt->execute();
        $this->users = $stmt->fetchAll();
    }

    private function loadProjects() {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE manager_id = ?");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $this->projects = $stmt->fetchAll();
    }

    private function handleTaskCreation() {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $assigned_to = $_POST['assigned_to'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $due_date = $_POST['due_date'];
        $status = $_POST['status'];
        $project_id = $_POST['project_id'];
        $manager_id = $_SESSION['user']['user_id'];

        try {
            $stmt = $this->pdo->prepare("INSERT INTO tasks (title, description, assigned_user_id, start_date, end_date, status, project_id) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $assigned_to, $start_date, $end_date, $status, $project_id]);

            $task_id = $this->pdo->lastInsertId();

            $stmt_deadline = $this->pdo->prepare("INSERT INTO deadlines (task_id, due_date) VALUES (?, ?)");
            $stmt_deadline->execute([$task_id, $due_date]);

$notificationMsg = " تم تكليفك بمهمة جديدة: {$title}";
$stmtNotif = $this->pdo->prepare("INSERT INTO notifications (user_id, content) VALUES (?, ?)");
$stmtNotif->execute([$assigned_to, $notificationMsg]);


            header("Location: manage_tasks.php");
            exit;

        } catch (PDOException $e) {
            $this->error = "فشل في إضافة المهمة: " . $e->getMessage();
        }
    }
}

$taskManager = new TaskManager($pdo);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة مهمة جديدة</title>
    <style>
        body { font-family: 'Cairo', sans-serif;
		background-color: #f0f0f0; 
		padding: 20px;
		}
        form { background: #fff; 
		max-width: 600px;
		margin: auto; 
		padding: 30px;
		border-radius: 10px; 
		box-shadow: 0 0 15px rgba(0,0,0,0.1);
		}
        input, textarea, select { width: 100%;
		padding: 10px;
		margin: 10px 0;
		border-radius: 5px;
		border: 1px solid #ccc;
		}
        button { padding: 12px 20px;
		background: #1abc9c;
		color: white;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		}
        button:hover { 
		background: #16a085;
		}
        .error { color: red;
		text-align: center;
		margin-top: 10px;
		}
        h2 { 
		text-align: center; 
		color: #333;
		}
    </style>
</head>
<body>

<h2>إضافة مهمة جديدة</h2>
<?php if ($taskManager->error): ?>
    <p class="error"><?= $taskManager->error ?></p>
<?php endif; ?>

<form method="POST">
    <label>عنوان المهمة:</label>
    <input type="text" name="title" required>

    <label>الوصف:</label>
    <textarea name="description" rows="4" required></textarea>

    <label>المستخدم المكلف:</label>
    <select name="assigned_to" required>
        <?php foreach ($taskManager->users as $user): ?>
            <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>تاريخ البدء:</label>
    <input type="date" name="start_date" required>

    <label>تاريخ الانتهاء:</label>
    <input type="date" name="end_date" required>

    <label>الموعد النهائي:</label>
    <input type="date" name="due_date" required>

    <label>الحالة:</label>
    <select name="status" required>
        <option value="معلقة">معلقة</option>
        <option value="جارية">جارية</option>
        <option value="مكتملة">مكتملة</option>
    </select>

    <label>المشروع:</label>
    <select name="project_id" required>
        <?php foreach ($taskManager->projects as $project): ?>
            <option value="<?= $project['project_id'] ?>"><?= htmlspecialchars($project['title']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">إضافة المهمة</button>
</form>

</body>
</html>
