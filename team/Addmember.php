<?php
require_once 'AuthManager.php';
require_once 'data.php';
require_once 'class_team.php';

AuthManager::requireRole('مدير مشروع');

$teamManager = new TeamManagement($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'], $_POST['team_id'])) {
    $teamManager->addMemberToTeam(trim($_POST['user_name']), intval($_POST['team_id']));
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة عضو</title>
</head>
<body>
    <h2>إضافة عضو إلى الفريق</h2>
    <form method="POST">
        <label>اسم العضو:</label>
        <input type="text" name="user_name" required>
        <label>الفريق:</label>
        <select name="team_id" required>
            <?php
            $stmt = $pdo->query("SELECT * FROM teams");
            while ($team = $stmt->fetch()) {
                echo "<option value='{$team['team_id']}'>{$team['team_name']}</option>";
            }
            ?>
        </select>
        <button type="submit">إضافة</button>
    </form>

    <?php if ($teamManager->success): ?>
        <p style="color:green;"><?= $teamManager->success ?></p>
    <?php elseif ($teamManager->error): ?>
        <p style="color:red;"><?= $teamManager->error ?></p>
    <?php endif; ?>
</body>
</html>
