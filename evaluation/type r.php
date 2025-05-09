<?php
include 'database.php';
session_start();

// كلاس لإدارة التقييمات
class EvaluationManager {
    private $pdo;
    private $user_id;
    private $request_id;
    private $project_id;

    public function __construct($pdo, $user_id, $request_id, $project_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
        $this->request_id = $request_id;
        $this->project_id = $project_id;
    }

    // تحقق إذا كان الطالب قد قام بالتقييم مسبقًا
    public function hasEvaluated() {
        $stmt = $this->pdo->prepare("SELECT * FROM evaluations WHERE request_id = ? AND user_id = ?");
        $stmt->execute([$this->request_id, $this->user_id]);
        return $stmt->rowCount() > 0;
    }

    // إضافة التقييم الجديد
    public function addEvaluation($score, $feedback) {
        try {
            $insert = $this->pdo->prepare("INSERT INTO evaluations (request_id, project_id, user_id, score, feedback, submitted_at)
                                          VALUES (?, ?, ?, ?, ?, NOW())");
            $insert->execute([$this->request_id, $this->project_id, $this->user_id, $score, $feedback]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// تحقق من صلاحية الطالب
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'طالب') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$request_id = $_GET['request_id'] ?? null;
$project_id = $_GET['project_id'] ?? null;

// التحقق من الطلب والمشروع
if (!$request_id || !$project_id) {
    die("طلب غير صالح.");
}

// إنشاء كائن لإدارة التقييمات
$evaluationManager = new EvaluationManager($pdo, $user_id, $request_id, $project_id);

// التحقق مما إذا كان الطالب قد قام بالتقييم مسبقًا
if ($evaluationManager->hasEvaluated()) {
    die("لقد قمت بتقييم هذا المشروع مسبقًا.");
}

// التعامل مع إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = $_POST['score'];
    $feedback = $_POST['feedback'];

    if ($evaluationManager->addEvaluation($score, $feedback)) {
        echo "<script>alert('تم إرسال تقييمك بنجاح'); window.location.href='view_requests.php';</script>";
        exit;
    } else {
        echo "<script>alert('حدث خطأ أثناء إرسال التقييم.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقييم المشروع</title>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #eef2f3; padding: 40px; text-align: center; }
        form { background: white; width: 60%; margin: auto; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        textarea, select { width: 100%; padding: 10px; margin-top: 10px; border-radius: 6px; border: 1px solid #ccc; }
        button { padding: 12px 25px; background-color: #2ecc71; color: white; border: none; border-radius: 6px; margin-top: 20px; font-size: 16px; }
        button:hover { background-color: #27ae60; }
    </style>
</head>
<body>

<h2>نموذج تقييم المشروع</h2>

<form method="POST">
    <label for="score">التقييم (من 1 إلى 5):</label>
    <select name="score" required>
        <option value="">اختر التقييم</option>
        <option value="1">1 - ضعيف</option>
        <option value="2">2 - مقبول</option>
        <option value="3">3 - جيد</option>
        <option value="4">4 - جيد جدًا</option>
        <option value="5">5 - ممتاز</option>
    </select>

    <label for="feedback">ملاحظات:</label>
    <textarea name="feedback" rows="5" placeholder="اكتب ملاحظاتك هنا..." required></textarea>

    <button type="submit">إرسال التقييم</button>
</form>

</body>
</html>
