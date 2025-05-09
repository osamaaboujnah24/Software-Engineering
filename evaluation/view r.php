<?php
include 'database.php';
session_start();

// تحقق من صلاحية مدير المشروع
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

$manager_id = $_SESSION['user']['user_id'];
$project_id = $_GET['project_id'] ?? null;
$request_id = $_GET['request_id'] ?? null;
$project = null;
$request = null;
$evaluations = [];

// جلب مشاريع هذا المدير فقط
$stmt = $pdo->prepare("SELECT project_id, title FROM projects WHERE manager_id = ?");
$stmt->execute([$manager_id]);
$projects = $stmt->fetchAll();

// إذا تم اختيار مشروع وطلب تقييم
if ($project_id && $request_id) {
    // جلب اسم المشروع واسم التقييم
    $stmt = $pdo->prepare("
        SELECT p.title AS project_title, er.title AS evaluation_title 
        FROM evaluation_requests er
        JOIN projects p ON p.project_id = er.project_id
        WHERE er.request_id = ? AND er.project_id = ? AND p.manager_id = ?
    ");
    $stmt->execute([$request_id, $project_id, $manager_id]);
    $request = $stmt->fetch();

    if ($request) {
        // جلب التقييمات
        $stmt_evals = $pdo->prepare("
            SELECT u.full_name, e.score, e.feedback, e.submitted_at
            FROM evaluations e
            JOIN users u ON e.user_id = u.user_id
            WHERE e.project_id = ? AND e.request_id = ?
        ");
        $stmt_evals->execute([$project_id, $request_id]);
        $evaluations = $stmt_evals->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عرض تقييمات المشاريع</title>
    <style>
        body { font-family: 'Cairo', sans-serif; padding: 30px; background: #f9f9f9; text-align: center; }
        select, button { padding: 10px; font-size: 16px; margin: 10px; }
        table { width: 90%; margin: 20px auto; border-collapse: collapse; background: white; box-shadow: 0 0 10px #ccc; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #1abc9c; color: white; }
        tr:hover td { background-color: #f1f1f1; }
        .no-data { margin-top: 20px; color: #888; font-size: 18px; }
    </style>
</head>
<body>

<h2>عرض تقييمات المشاريع</h2>

<form method="GET">
    <label>اختر المشروع:</label>
    <select name="project_id" required onchange="this.form.submit()">
        <option value="">-- اختر مشروعًا --</option>
        <?php foreach ($projects as $p): ?>
            <option value="<?= $p['project_id'] ?>" <?= $p['project_id'] == $project_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['title']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if ($project_id): ?>
        <br><label>اختر التقييم:</label>
        <select name="request_id" required>
            <option value="">-- اختر تقييم --</option>
            <?php
            $stmt_req = $pdo->prepare("SELECT request_id, title FROM evaluation_requests WHERE project_id = ?");
            $stmt_req->execute([$project_id]);
            foreach ($stmt_req->fetchAll() as $r):
            ?>
                <option value="<?= $r['request_id'] ?>" <?= $r['request_id'] == $request_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">عرض</button>
    <?php endif; ?>
</form>

<?php if ($request): ?>
    <h3>التقييم: <strong><?= isset($request['evaluation_title']) ? htmlspecialchars($request['evaluation_title']) : 'غير متوفر' ?></strong> لمشروع: <?= isset($request['project_title']) ? htmlspecialchars($request['project_title']) : 'غير متوفر' ?></h3>

    <?php if (count($evaluations) > 0): ?>
        <table>
            <tr>
                <th>اسم العضو</th>
                <th>التقييم</th>
                <th>الملاحظات</th>
                <th>تاريخ التقديم</th>
            </tr>
            <?php foreach ($evaluations as $eval): ?>
                <tr>
                    <td><?= htmlspecialchars($eval['full_name']) ?></td>
                    <td><?= htmlspecialchars($eval['score']) ?> / 5</td>
                    <td><?= nl2br(htmlspecialchars($eval['feedback'])) ?></td>
                    <td><?= $eval['submitted_at'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p class="no-data">لا توجد تقييمات بعد لهذا التقييم.</p>
    <?php endif; ?>
<?php elseif ($project_id && !$request_id): ?>
    <p class="no-data">يرجى اختيار التقييم لعرض النتائج.</p>
<?php endif; ?>

</body>
</html>
