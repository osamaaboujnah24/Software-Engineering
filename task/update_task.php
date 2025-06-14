<?php
include 'data.php';
require_once 'AuthManager.php';
$pdo = Database::getInstance()->getConnection();


AuthManager::requireRole('مدير مشروع');

class EditTaskHandler {
    private $pdo;
    private $task_id;
    private $project_id;
    public $task;
    public $users;
    public $error = '';

    public function __construct($pdo, $task_id, $project_id) {
        $this->pdo = $pdo;
        $this->task_id = $task_id;
        $this->project_id = $project_id;
    }

    public function loadTask() {
        if (!$this->task_id) {
            echo "لم يتم تحديد المهمة.";
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE task_id = ?");
        $stmt->execute([$this->task_id]);
        $this->task = $stmt->fetch();

        if (!$this->task) {
            echo "لم يتم العثور على المهمة.";
            exit;
        }
    }

    public function loadUsers() {
        $stmt = $this->pdo->prepare("SELECT user_id, full_name FROM users WHERE role != 'مدير مشروع'");
        $stmt->execute();
        $this->users = $stmt->fetchAll();
    }

    public function handleFormSubmission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$title, $description, $assigned_user_id, $start_date, $end_date, $status] = $this->getFormInput();

            try {
                $this->updateTaskInDatabase($title, $description, $assigned_user_id, $start_date, $end_date, $status);
                $this->notifyUser($assigned_user_id, $title);

                header("Location: viewtasks.php?id=" . $this->project_id);
                exit;
            } catch (PDOException $e) {
                $this->error = "خطأ أثناء تعديل المهمة: " . $e->getMessage();
            }
        }
    }

    private function getFormInput() {
        return [
            $_POST['title'],
            $_POST['description'],
            $_POST['assigned_user_id'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['status']
        ];
    }

    private function updateTaskInDatabase($title, $description, $assigned_user_id, $start_date, $end_date, $status) {
        $stmt = $this->pdo->prepare("UPDATE tasks SET title = ?, description = ?, assigned_user_id = ?, start_date = ?, end_date = ?, status = ? WHERE task_id = ?");
        $stmt->execute([$title, $description, $assigned_user_id, $start_date, $end_date, $status, $this->task_id]);
    }

    private function notifyUser($assigned_user_id, $title) {
        $notificationMsg = "تم تعديل المهمة '{$title}' التي كُلفت بها.";
        $stmtNotif = $this->pdo->prepare("INSERT INTO notifications (user_id, content) VALUES (?, ?)");
        $stmtNotif->execute([$assigned_user_id, $notificationMsg]);
    }
}

$handler = new EditTaskHandler($pdo, $_GET['task_id'] ?? null, $_GET['project_id'] ?? null);
$handler->loadTask();
$handler->loadUsers();
$handler->handleFormSubmission();
$task = $handler->task;
$users = $handler->users;
$error = $handler->error;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل المهمة</title>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f4f4f4; padding: 20px; }
        form { background: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto; }
        input, textarea, select { width: 100%; padding: 10px; margin-bottom: 10px; }
        button { padding: 10px 20px; background-color: #1abc9c; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #16a085; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<h2>📝 تعديل المهمة</h2>

<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <label>عنوان المهمة:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>

    <label>الوصف:</label>
    <textarea name="description" required><?= htmlspecialchars($task['description']) ?></textarea>

    <label>المستخدم المكلف:</label>
    <select name="assigned_user_id" required>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['user_id'] ?>" <?= $user['user_id'] == $task['assigned_user_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['full_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>تاريخ البدء:</label>
    <input type="date" name="start_date" value="<?= $task['start_date'] ?>" required>

    <label>تاريخ الانتهاء:</label>
    <input type="date" name="end_date" value="<?= $task['end_date'] ?>" required>

    <label>الحالة:</label>
    <select name="status" required>
        <option value="معلقة" <?= $task['status'] == 'معلقة' ? 'selected' : '' ?>>معلقة</option>
        <option value="جارية" <?= $task['status'] == 'جارية' ? 'selected' : '' ?>>جارية</option>
        <option value="مكتملة" <?= $task['status'] == 'مكتملة' ? 'selected' : '' ?>>مكتملة</option>
    </select>

    <button type="submit"> حفظ التعديل</button>
</form>

</body>
</html>
