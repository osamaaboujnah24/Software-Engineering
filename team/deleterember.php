<?php
require_once 'AuthManager.php';
require_once 'data.php';
require_once 'class_team.php';

AuthManager::requireRole('مدير مشروع');

$teamManager = new TeamManagement($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['team_id'])) {
    $teamManager->removeMemberFromTeam(intval($_POST['team_id']), intval($_POST['user_id']));
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>حذف عضو</title>
</head>
<body>
    <h2>حذف عضو من الفريق</h2>
    <form method="POST">
        <label>اختر الفريق:</label>
        <select name="team_id" required>
            <?php
            $stmt = $pdo->query("SELECT * FROM teams");
            while ($team = $stmt->fetch()) {
                echo "<option value='{$team['team_id']}'>{$team['team_name']}</option>";
            }
            ?>
        </select>

        <label>اختر العضو:</label>
        <select name="user_id" required>
            <?php
            $stmt = $pdo->query("SELECT user_id, full_name FROM users");
            while ($user = $stmt->fetch()) {
                echo "<option value='{$user['user_id']}'>{$user['full_name']}</option>";
            }
            ?>
        </select>

        <button type="submit">حذف</button>
    </form>

    <?php if ($teamManager->success): ?>
        <p style="color:green;"><?= $teamManager->success ?></p>
    <?php elseif ($teamManager->error): ?>
        <p style="color:red;"><?= $teamManager->error ?></p>
    <?php endif; ?>
</body>
</html>
