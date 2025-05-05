<?php
include 'database.php';
session_start();

class TaskNote {
    private $pdo;
    private $task_id;
    private $user_id;

    public function __construct($pdo, $task_id, $user_id) {
        $this->pdo = $pdo;
        $this->task_id = $task_id;
        $this->user_id = $user_id;
    }

    // إضافة ملاحظة جديدة
    public function addComment($content) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO comments (content, user_id, task_id, comment_date) 
                                         VALUES (?, ?, ?, NOW())");
            $stmt->execute([$content, $this->user_id, $this->task_id]);
            return true;
        } catch (PDOException $e) {
            throw new Exception(" فشل في إضافة الملاحظة: " . $e->getMessage());
        }
    }

    // جلب الملاحظات الخاصة بالمهمة
    public function getComments() {
        try {
            $stmt_comments = $this->pdo->prepare("
                SELECT comments.*, users.full_name 
                FROM comments 
                JOIN users ON comments.user_id = users.user_id 
                WHERE comments.task_id = ?
            ");
            $stmt_comments->execute([$this->task_id]);
            return $stmt_comments->fetchAll();
        } catch (PDOException $e) {
            throw new Exception(" فشل في جلب الملاحظات: " . $e->getMessage());
        }
    }
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'طالب') {
    header("Location: login.php");
    exit;
}

$task_id = $_GET['task_id'] ?? null;
$user_id = $_SESSION['user']['user_id']; // جلب id المستخدم من الجلسة

if ($task_id) {
    $taskNote = new TaskNote($pdo, $task_id, $user_id);

    // إضافة الملاحظة
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $content = $_POST['content'];

        try {
            if ($taskNote->addComment($content)) {
                header("Location: task_note.php?task_id=" . $task_id);
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // جلب الملاحظات
    try {
        $comments = $taskNote->getComments();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
} else {
    echo "لم يتم تحديد المهمة.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة ملاحظة للمهمة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        header {
            background-color: #1abc9c;
            color: white;
            padding: 25px 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        header h2 {
            margin: 0;
            font-size: 28px;
        }

        form {
            background-color: white;
            padding: 20px;
            margin: 30px auto;
            width: 60%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        button {
            padding: 10px 20px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #16a085;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            background-color: white;
            padding: 10px;
            margin: 10px auto;
            width: 60%;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            text-align: right;
        }

        ul li small {
            color: #777;
        }
    </style>
</head>
<body>

<header>
    <h2>إضافة ملاحظة للمهمة</h2>
</header>

<div>
    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="content">الملاحظة:</label>
        <textarea name="content" rows="4" required></textarea>
        <button type="submit">إضافة الملاحظة</button>
    </form>

    
    <ul>
        <?php foreach ($comments as $comment): ?>
         
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
