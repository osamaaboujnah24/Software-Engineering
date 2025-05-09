<?php
include 'database.php';
session_start();

// كلاس إدارة طلبات التقييم
class EvaluationRequestManager {
    private $pdo;
    private $user_id;

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
    }

    // جلب الطلبات الموجهة للفريق الذي ينتمي إليه الطالب
    public function getRequests() {
        $stmt = $this->pdo->prepare("
            SELECT er.request_id, er.project_id, er.title AS eval_title, p.title AS project_title
            FROM evaluation_requests er
            JOIN projects p ON er.project_id = p.project_id
            JOIN teams t ON p.team_id = t.team_id
            JOIN team_members tm ON t.team_id = tm.team_id
            WHERE tm.user_id = ?
        ");
        $stmt->execute([$this->user_id]);
        return $stmt->fetchAll();
    }
}

// التحقق من صلاحية الطالب
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'طالب') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

// إنشاء كائن لإدارة طلبات التقييم
$evaluationRequestManager = new EvaluationRequestManager($pdo, $user_id);

// جلب الطلبات
$requests = $evaluationRequestManager->getRequests();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلبات التقييم</title>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f7f7f7; padding: 30px; text-align: center; }
        table { width: 80%; margin: auto; border-collapse: collapse; background-color: #fff; box-shadow: 0 0 10px #ccc; }
        th, td { padding: 15px; border: 1px solid #ddd; }
        th { background-color: #1abc9c; color: white; }
        a.button { padding: 8px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 6px; }
        a.button:hover { background-color: #2980b9; }
    </style>
</head>
<body>

<h2>الطلبات المتاحة للتقييم</h2>

<?php if (count($requests) > 0): ?>
    <table>
        <tr>
            <th>المشروع</th>
            <th>اسم التقييم</th>
            <th>الإجراء</th>
        </tr>
        <?php foreach ($requests as $req): ?>
        <tr>
            <td><?= htmlspecialchars($req['project_title']) ?></td>
            <td><?= htmlspecialchars($req['eval_title']) ?></td>
            <td>
                <a class="button" href="type r.php?request_id=<?= $req['request_id'] ?>&project_id=<?= $req['project_id'] ?>">قيّم المشروع</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>لا توجد طلبات تقييم متاحة حاليًا.</p>
<?php endif; ?>

</body>
</html>
