<?php
include 'database.php';
session_start();

// كلاس مدير المشروع
class ProjectManager {
    private $pdo;
    private $manager_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->manager_id = $_SESSION['user']['user_id'];

        // التحقق من صلاحية المدير
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
            header("Location: login.php");
            exit;
        }
    }

    // جلب المشاريع الخاصة بالمدير
    public function getProjects() {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE manager_id = ?");
        $stmt->execute([$this->manager_id]);
        return $stmt->fetchAll();
    }

    // جلب الفرق الخاصة بالمشروع
    public function getTeams($project_id) {
        $stmt = $this->pdo->prepare("SELECT t.team_id, t.team_name 
                                     FROM teams t 
                                     JOIN projects p ON p.team_id = t.team_id
                                     WHERE p.project_id = ? AND p.manager_id = ?");
        $stmt->execute([$project_id, $this->manager_id]);
        return $stmt->fetchAll();
    }

    // إرسال طلب التقييم
    public function sendEvaluationRequest($project_id, $team_id, $title) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO evaluation_requests (project_id, team_id, manager_id, request_date, title) 
                                         VALUES (?, ?, ?, NOW(), ?)");
            $stmt->execute([$project_id, $team_id, $this->manager_id, $title]);
            return "✅ تم إرسال طلب التقييم بنجاح بعنوان: $title";
        } catch (PDOException $e) {
            return "❌ فشل في إرسال الطلب: " . $e->getMessage();
        }
    }
}

// إنشاء كائن مدير المشروع
$projectManager = new ProjectManager($pdo);

$projects = $projectManager->getProjects();
$teams = [];
$error = '';
$success = '';

// التحقق من اختيار المشروع
if (isset($_GET['project_id'])) {
    $selected_project_id = $_GET['project_id'];
    $teams = $projectManager->getTeams($selected_project_id);
}

// عند إرسال الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $team_id = $_POST['team_id'];
    $title = $_POST['title'];

    $result = $projectManager->sendEvaluationRequest($project_id, $team_id, $title);
    if (strpos($result, 'تم إرسال') !== false) {
        $success = $result;
    } else {
        $error = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إرسال طلب تقييم</title>
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f4f7fc; padding: 20px; text-align: center; }
        form { background: white; max-width: 600px; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px #ccc; }
        select, input, button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #1abc9c; color: white; border: none; cursor: pointer; }
        button:hover { background: #16a085; }
        .msg { margin: 10px 0; font-weight: bold; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<h2>إرسال طلب تقييم لفريق</h2>

<?php if ($error): ?><p class="msg error"><?= $error ?></p><?php endif; ?>
<?php if ($success): ?><p class="msg success"><?= $success ?></p><?php endif; ?>

<form method="POST">
    <label for="project_id">اختر المشروع:</label>
    <select name="project_id" onchange="location = '?project_id=' + this.value;" required>
        <option value="">-- اختر مشروع --</option>
        <?php foreach ($projects as $proj): ?>
            <option value="<?= $proj['project_id'] ?>" <?= isset($selected_project_id) && $selected_project_id == $proj['project_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($proj['title']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (!empty($teams)): ?>
        <label for="title">عنوان التقييم (مثال: تقييم الواجب 3):</label>
        <input type="text" name="title" required>

        <label for="team_id">اختر الفريق:</label>
        <select name="team_id" required>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">إرسال طلب التقييم</button>
    <?php endif; ?>
</form>

</body>
</html>
