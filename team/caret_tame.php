<?php
include 'data.php';
session_start();

class TeamCreation {
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

    public function createTeam($team_name) {
        try {
            $check = $this->pdo->prepare("SELECT * FROM teams WHERE team_name = ?");
            $check->execute([$team_name]);

            if ($check->rowCount() > 0) {
                $this->error = " اسم الفريق موجود مسبقًا.";
                return;
            }

            $stmt = $this->pdo->prepare("INSERT INTO teams (team_name) VALUES (?)");
            $stmt->execute([$team_name]);

            $this->success = " تم إنشاء الفريق بنجاح.";
        } catch (PDOException $e) {
            $this->error = " خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    }
}

$teamCreator = new TeamCreation($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_name'])) {
    $teamCreator->createTeam(trim($_POST['team_name']));
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إنشاء فريق جديد</title>
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

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>إنشاء فريق جديد</h2>
    <form method="POST">
        <label for="team_name">اسم الفريق:</label>
        <input type="text" name="team_name" id="team_name" required>
        <button type="submit">إنشاء</button>
    </form>

    <?php if ($teamCreator->success): ?>
        <p class="message"><?= $teamCreator->success ?></p>
    <?php elseif ($teamCreator->error): ?>
        <p class="message error"><?= $teamCreator->error ?></p>
    <?php endif; ?>
</div>

</body>
</html>
