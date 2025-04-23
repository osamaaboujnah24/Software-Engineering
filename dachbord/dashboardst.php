<?php
include 'data.php';
session_start();

class StudentDashboard {
    private $pdo;
    private $user_id;
    public $project;
    public $progress_percent = 0;
    public $tasks = [];

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
    }

    public function loadDashboardData() {
        try {
            $stmt_project = $this->pdo->prepare("SELECT * FROM projects WHERE team_id IN (SELECT team_id FROM team_members WHERE user_id = ?)");
            $stmt_project->execute([$this->user_id]);
            $this->project = $stmt_project->fetch();

            if ($this->project) {
                $stmt_progress = $this->pdo->prepare("SELECT progress_percent FROM progress_board WHERE project_id = ?");
                $stmt_progress->execute([$this->project['project_id']]);
                $progress = $stmt_progress->fetch();
                $this->progress_percent = $progress ? $progress['progress_percent'] : 0;

                $stmt_tasks = $this->pdo->prepare("SELECT * FROM tasks WHERE assigned_user_id = ?");
                $stmt_tasks->execute([$this->user_id]);
                $this->tasks = $stmt_tasks->fetchAll();
            }
        } catch (PDOException $e) {
            die("حدث خطأ في الاتصال أو تنفيذ الاستعلام: " . $e->getMessage());
        }
    }
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'طالب') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$dashboard = new StudentDashboard($pdo, $user_id);
$dashboard->loadDashboardData();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الطالب</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #ffffff;
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        header {
            background-color: #2ecc71;
            color: white;
            padding: 25px 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        header h2 {
            margin: 0;
            font-size: 28px;
        }

        h3 {
            color: #2c3e50;
            margin-top: 20px;
        }

        p {
            font-size: 17px;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 14px;
            text-align: center;
            border: 1px solid #eee;
        }

        th {
            background-color: #2ecc71;
            color: white;
            font-size: 16px;
        }

        td {
            background-color: #f9f9f9;
            color: #333;
        }

        tr:hover td {
            background-color: #f1f1f1;
        }

        .logout-link {
            display: inline-block;
            margin: 30px auto;
            background-color: #e74c3c;
            padding: 12px 30px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .logout-link:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<header>
    <h2>مرحبًا، <?php echo htmlspecialchars($_SESSION['user']['full_name']); ?>!</h2>
</header>

<h3>مشروعك: <?php echo $dashboard->project ? htmlspecialchars($dashboard->project['title']) : 'لا يوجد مشروع'; ?></h3>
<p><strong>الوصف:</strong> <?php echo $dashboard->project ? htmlspecialchars($dashboard->project['description']) : 'لا يوجد وصف للمشروع'; ?></p>

<h3>نسبة التقدم في المشروع: <?php echo $dashboard->progress_percent; ?>%</h3>

<h3>المهام المسندة إليك:</h3>
<table>
    <tr>
        <th>العنوان</th>
        <th>الوصف</th>
        <th>الفترة</th>
        <th>الحالة</th>
    </tr>
    <?php foreach ($dashboard->tasks as $task): ?>
        <tr>
            <td><?php echo htmlspecialchars($task['title']); ?></td>
            <td><?php echo htmlspecialchars($task['description']); ?></td>
            <td><?php echo $task['start_date'] . ' إلى ' . $task['end_date']; ?></td>
            <td><?php echo htmlspecialchars($task['status']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="../logout.php" class="logout-link">تسجيل الخروج</a>

</body>
</html>