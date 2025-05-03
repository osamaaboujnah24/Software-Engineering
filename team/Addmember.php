<?php
include 'data.php';
session_start();

class TeamManagement {
    private $pdo;
    public $error = '';
    public $success = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
            header("Location: login.php");
            exit;
        }
    }

    public function addMemberToTeam($user_name, $team_id) {
        try {
            // التحقق من وجود المستخدم
            $stmt_user = $this->pdo->prepare("SELECT user_id FROM users WHERE full_name = ?");
            $stmt_user->execute([$user_name]);
            $user = $stmt_user->fetch();

            if (!$user) {
                $this->error = " العضو غير موجود  .";
                return;
            }

            // التحقق إذا كان موجود بالفعل في الفريق
            $check = $this->pdo->prepare("SELECT * FROM team_members WHERE team_id = ? AND user_id = ?");
            $check->execute([$team_id, $user['user_id']]);
            if ($check->rowCount() > 0) {
                $this->error = " العضو موجود بالفعل   .";
                return;
            }

            // الإضافة
            $stmt_add = $this->pdo->prepare("INSERT INTO team_members (team_id, user_id) VALUES (?, ?)");
            $stmt_add->execute([$team_id, $user['user_id']]);

            $this->success = " تم إضافة  '$user_name' إلى الفريق بنجاح.";

        } catch (PDOException $e) {
            $this->error = " خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    }
}

$teamManager = new TeamManagement($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_id'], $_POST['user_name'])) {
    $teamManager->addMemberToTeam(trim($_POST['user_name']), intval($_POST['team_id']));
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة عضو إلى الفريق</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>إضافة عضو إلى الفريق</h2>
    <form method="POST">
        <label for="team_id">اختر الفريق:</label>
        <select name="team_id" id="team_id" required>
            <?php
            $stmt = $pdo->query("SELECT * FROM teams");
            while ($team = $stmt->fetch()) {
                echo "<option value='{$team['team_id']}'>{$team['team_name']}</option>";
            }
            ?>
        </select>

        <label for="user_name">اسم العضو:</label>
        <input type="text" name="user_name" id="user_name" placeholder="مثال: أحمد محمد" required>

        <button type="submit">إضافة العضو</button>
    </form>

    <?php if ($teamManager->success): ?>
        <p class="message success"><?= $teamManager->success ?></p>
    <?php elseif ($teamManager->error): ?>
        <p class="message error"><?= $teamManager->error ?></p>
    <?php endif; ?>
</div>

</body>
</html>
