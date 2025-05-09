<?php
include 'data.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض أعضاء الفريق</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            padding: 40px;
            text-align: center;
        }

        .form-box {
            margin-bottom: 30px;
        }

        select {
            padding: 10px;
            width: 250px;
            border-radius: 8px;
            font-size: 16px;
        }

        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 70%;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <h2>عرض أعضاء الفريق</h2>

    <div class="form-box">
        <form method="POST">
            <label for="team_id">اختر الفريق:</label>
            <select name="team_id" id="team_id" onchange="this.form.submit()" required>
                <option value="">-- اختر فريقًا --</option>
                <?php
                $teams = $pdo->query("SELECT * FROM teams")->fetchAll();
                foreach ($teams as $team) {
                    $selected = (isset($_POST['team_id']) && $_POST['team_id'] == $team['team_id']) ? "selected" : "";
                    echo "<option value='{$team['team_id']}' $selected>{$team['team_name']}</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <?php if (!empty($_POST['team_id'])): ?>
        <table>
            <thead>
                <tr>
                    <th>رقم العضو</th>
                    <th>الاسم الكامل</th>
                    <th>البريد الإلكتروني</th>
                    <th>الدور</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("
                    SELECT users.user_id, users.full_name, users.email, users.role 
                    FROM team_members 
                    JOIN users ON team_members.user_id = users.user_id 
                    WHERE team_members.team_id = ?
                ");
                $stmt->execute([$_POST['team_id']]);
                $members = $stmt->fetchAll();

                if (count($members) > 0) {
                    foreach ($members as $user) {
                        echo "<tr>
                                <td>{$user['user_id']}</td>
                                <td>{$user['full_name']}</td>
                                <td>{$user['email']}</td>
                                <td>{$user['role']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>لا يوجد أعضاء   .</td></tr>";
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
