<?php
include 'database.php';
session_start();

// تحقق من صلاحية المستخدم
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'طالب') {
    header("Location: login.php");
    exit;
}

// الحصول على معرف المهمة
$task_id = $_GET['task_id'] ?? null;
$user_id = $_SESSION['user']['user_id'];

if (!$task_id) {
    die("لم يتم تحديد المهمة.");
}

// جلب بيانات المهمة (اختياري للعرض)
$stmt_task = $pdo->prepare("SELECT * FROM tasks WHERE task_id = ?");
$stmt_task->execute([$task_id]);
$task = $stmt_task->fetch();

// إضافة ملاحظة جديدة
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO comments (content, user_id, task_id, comment_date) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$content, $user_id, $task_id]);
        header("Location: task_notes.php?task_id=" . $task_id);
        exit;
    }
}

// جلب الملاحظات المرتبطة بالمهمة مع اسم المستخدم
$stmt_comments = $pdo->prepare("
    SELECT comments.*, users.full_name 
    FROM comments 
    JOIN users ON comments.user_id = users.user_id 
    WHERE comments.task_id = ?
    ORDER BY comment_date DESC
");
$stmt_comments->execute([$task_id]);
$comments = $stmt_comments->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملاحظات المهمة</title>
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

        .task-title {
            margin-top: 15px;
            font-size: 20px;
            color: #2c3e50;
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

        .add-note-form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 70%;
            margin: 30px auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: right;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            resize: vertical;
        }

        button {
            margin-top: 10px;
            background-color: #1abc9c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #16a085;
        }
    </style>
</head>
<body>

<header>
    <h2>ملاحظات المهمة</h2>
</header>

<?php if ($task): ?>
    <div class="task-title">مهمة: <strong><?php echo htmlspecialchars($task['title']); ?></strong></div>
<?php endif; ?>

<div class="comments-container">
    <h3>الملاحظات:</h3>
    <?php if ($comments): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                <small>بواسطة: <?php echo htmlspecialchars($comment['full_name']); ?> - 
                       بتاريخ: <?php echo htmlspecialchars($comment['comment_date']); ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>لا توجد ملاحظات بعد.</p>
    <?php endif; ?>
</div>

<div class="add-note-form">
    <h3>إضافة ملاحظة جديدة:</h3>
    <form method="POST">
        <textarea name="content" rows="4" placeholder="اكتب ملاحظتك هنا..." required></textarea>
        <br>
        <button type="submit">إضافة</button>
    </form>
</div>

</body>
</html>
