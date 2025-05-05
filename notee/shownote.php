<?php
include 'database.php';
session_start();

class TaskNote {
    private $pdo;
    private $task_id;

    public function __construct($pdo, $task_id) {
        $this->pdo = $pdo;
        $this->task_id = $task_id;
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

if ($task_id) {
    $taskNote = new TaskNote($pdo, $task_id);

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
    <title>عرض ملاحظات المهمة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1abc9c;
            color: white;
            padding: 20px 0;
        }

        h2 {
            margin: 0;
        }

        .comments-container {
            width: 70%;
            margin: 20px auto;
            text-align: right;
        }

        .comment {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .comment small {
            color: #777;
        }

    </style>
</head>
<body>

<header>
    <h2>عرض ملاحظات المهمة</h2>
</header>

<div class="comments-container">
    <h3>الملاحظات:</h3>
    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php elseif (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                <small>بواسطة: <?php echo htmlspecialchars($comment['full_name']); ?> - بتاريخ: <?php echo $comment['comment_date']; ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>لا توجد ملاحظات لهذا المهمة بعد.</p>
    <?php endif; ?>
</div>

</body>
</html>
