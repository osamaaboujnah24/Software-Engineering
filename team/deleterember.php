<?php
include 'data.php';
session_start();

class TeamMemberRemoval {
    private $pdo;
    public $success = '';
    public $error = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
            header("Location: login.php");
            exit;
        }
    }

    public function removeMember($team_id, $user_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
            $stmt->execute([$team_id, $user_id]);

            if ($stmt->rowCount() > 0) {
                $this->success = " تم حذف العضو من الفريق .";
            } else {
                $this->error = " هذا العضو غير موجود في الفريق.";
            }
        } catch (PDOException $e) {
            $this->error = " خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    }
}

$manager = new TeamMemberRemoval($pdo);

// عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_id'], $_POST['user_id'])) {
    $manager->removeMember($_POST['team_id'], $_POST['user_id']);
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>حذف عضو من الفريق</title>
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

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #dc3545;
            border: none;
            color: white;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #c82333;
        }

        .message {
            text-align: center;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>حذف عضو من الفريق</h2>
    <form method="POST">
        <label for="team_id">اختر الفريق:</label>
        <select name="team_id" id="team_id" required onchange="this.form.submit()">
            <option value="">-- اختر فريقًا --</option>
            <?php
            $teams = $pdo->query("SELECT * FROM teams")->fetchAll();
            foreach ($teams as $team) {
                $selected = (isset($_POST['team_id']) && $_POST['team_id'] == $team['team_id']) ? "selected" : "";
                echo "<option value='{$team['team_id']}' $selected>{$team['team_name']}</option>";
            }
            ?>
        </select>

        <?php if (!empty($_POST['team_id'])): ?>
            <label for="user_id">اختر العضو:</label>
            <select name="user_id" id="user_id" required>
                <option value="">-- اختر عضوًا --</option>
                <?php
                $stmt = $pdo->prepare("SELECT users.user_id, users.full_name FROM team_members 
                                       JOIN users ON team_members.user_id = users.user_id
                                       WHERE team_members.team_id = ?");
                $stmt->execute([$_POST['team_id']]);
                foreach ($stmt->fetchAll() as $user) {
                    echo "<option value='{$user['user_id']}'>{$user['full_name']}</option>";
                }
                ?>
            </select>
            <button type="submit">حذف العضو</button>
        <?php endif; ?>
    </form>

    <?php if ($manager->success): ?>
        <p class="message success"><?= $manager->success ?></p>
    <?php elseif ($manager->error): ?>
        <p class="message error"><?= $manager->error ?></p>
    <?php endif; ?>
</div>

</body>
</html>
