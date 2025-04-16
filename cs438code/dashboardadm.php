<?php
include 'data.php';
session_start();

class AdminDashboard {
    private $pdo;
    public $projects = [];
    public $error = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function isAuthenticated() {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'مدير مشروع';
    }

    public function loadProjects() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM projects");
            $stmt->execute();
            $this->projects = $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->error = "فشل في جلب المشاريع: " . $e->getMessage();
        }
    }

    public function getProgress($project_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT progress_percent FROM progress_board WHERE project_id = ?");
            $stmt->execute([$project_id]);
            $result = $stmt->fetch();
            return $result ? $result['progress_percent'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}

$dashboard = new AdminDashboard($pdo);
if (!$dashboard->isAuthenticated()) {
    header("Location: login.php");
    exit;
}
$dashboard->loadProjects();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم مدير المشروع</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        nav {
            background-color: #2ecc71;
            padding: 15px 0;
        }

        nav a {
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 18px;
            margin: 0 10px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: #27ae60;
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

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 30px;
            gap: 20px;
        }

        .box {
            background-color: white;
            width: 250px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .box h3 {
            font-size: 20px;
            color: #2ecc71;
        }

        .box .percentage {
            font-size: 30px;
            color: #27ae60;
            margin-top: 10px;
        }

        .table-title {
            font-size: 22px;
            color: #2ecc71;
            margin-top: 40px;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #2ecc71;
            color: white;
        }

        td {
            background-color: #ffffff;
        }

        table tr:hover td {
            background-color: #f1f1f1;
        }

        .view-link {
            background-color: #27ae60;
            padding: 6px 15px;
            color: white;
            border-radius: 6px;
            text-decoration: none;
        }

        .view-link:hover {
            background-color: #1e874b;
        }
    </style>
</head>
<body>

<nav>
    <a href="dashboardadm.php">لوحة التحكم</a>
    <a href="project.php">إضافة مشروع جديد</a>
    <a href="logout.php">تسجيل الخروج</a>
</nav>

<header>
    <h2>مرحبًا، <?php echo htmlspecialchars($_SESSION['user']['full_name']); ?> (مدير مشروع)</h2>
</header>

<div class="container">
    <div class="box">
        <h3>إجمالي المشاريع</h3>
        <p class="percentage"><?php echo count($dashboard->projects); ?></p>
    </div>
    <div class="box">
        <h3>التقدم العام</h3>
        <p class="percentage">--%</p> <!-- تطوير لاحق -->
    </div>
</div>

<h3 class="table-title">المشاريع الحالية:</h3>
<table>
    <tr>
        <th>العنوان</th>
        <th>الوصف</th>
        <th>الفترة</th>
        <th>التقدم</th>
        <th>الإجراءات</th>
    </tr>
    <?php foreach ($dashboard->projects as $project): ?>
        <tr>
            <td><?php echo htmlspecialchars($project['title']); ?></td>
            <td><?php echo htmlspecialchars($project['description']); ?></td>
            <td><?php echo htmlspecialchars($project['start_date']) . " إلى " . htmlspecialchars($project['end_date']); ?></td>
            <td><?php echo $dashboard->getProgress($project['project_id']); ?>%</td>
            <td><a class="view-link" href="veiwproject.php?id=<?php echo $project['project_id']; ?>">عرض</a></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
