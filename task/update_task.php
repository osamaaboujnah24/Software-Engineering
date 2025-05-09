<?php
include 'data.php';
session_start();

class TaskManager {
    private $pdo;
    private $project_id;

    public function __construct($pdo, $project_id) {
        $this->pdo = $pdo;
        $this->project_id = $project_id;
    }

    // جلب المهام الخاصة بالمشروع
    public function getTasks() {
        try {
            $stmt = $this->pdo->prepare("SELECT tasks.*, users.full_name 
                                         FROM tasks 
                                         LEFT JOIN users ON tasks.assigned_user_id = users.user_id 
                                         WHERE tasks.project_id = ? 
                                         ORDER BY tasks.end_date");
            $stmt->execute([$this->project_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("فشل في جلب المهام: " . $e->getMessage());
        }
    }
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

$project_id = $_GET['id'] ?? null;

if ($project_id) {
    $taskManager = new TaskManager($pdo, $project_id);

    try {
        $tasks = $taskManager->getTasks();
    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }
} else {
    echo "لم يتم تحديد المشروع.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عرض المهام</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #1abc9c;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover td {
            background-color: #f1f1f1;
        }

        .back-link {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-link:hover {
            background-color: #0056b3;
        }

        .delete-link {
            color: red;
            text-decoration: none;
        }

        .delete-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>عرض المهام للمشروع</h2>

<a href="veiwproject.php?id=<?php echo $project_id; ?>" class="back-link">العودة إلى تفاصيل المشروع</a>

<?php if ($tasks): ?>
    <table>
        <thead>
            <tr>
                <th>عنوان المهمة</th>
                <th>الوصف</th>
                <th>المسؤول</th>
                <th>تاريخ البدء</th>
                <th>تاريخ الانتهاء</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td><?= htmlspecialchars($task['description']) ?></td>
                    <td><?= htmlspecialchars($task['full_name']) ?></td>
                    <td><?= $task['start_date'] ?></td>
                    <td><?= $task['end_date'] ?></td>
                    <td><?= $task['status'] ?></td>
                    <td>
                        <a href="edittask.php?task_id=<?= $task['task_id'] ?>&project_id=<?= $project_id ?>" class="edit-link">تعديل</a>
                        <a href="deletetask.php?task_id=<?= $task['task_id'] ?>" class="delete-link" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذه المهمة؟')">حذف</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>لا توجد مهام لهذا المشروع.</p>
<?php endif; ?>

</body>
</html>
