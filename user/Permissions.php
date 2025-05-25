<?php
session_start();
require_once 'Data.php';
$pdo = Database::getInstance()->getConnection();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';
$user_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $email, $role, $user_id]);
        $success = " تم تحديث بيانات  .";
    } catch (PDOException $e) {
        $error = " خطأ  التحديث: " . $e->getMessage();
    }
}

if (isset($_POST['select_user']) && $_POST['user_id']) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_POST['user_id']]);
    $user_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تعديل بيانات </title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f6f9;
            padding: 40px;
            text-align: center;
        }

        .form-box {
            background-color: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
            width: 400px;
            margin: 0 auto;
        }

        select, input[type="text"], input[type="email"] {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            font-weight: bold;
            margin-top: 15px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        h2 {
            color: #333;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>تعديل بيانات </h2>

    <form method="POST">
        <label>اختر العضو:</label>
        <select name="user_id" required onchange="this.form.submit()">
            <option value="">-- اختر عضوًا --</option>
            <?php
            $users = $pdo->query("SELECT user_id, full_name FROM users")->fetchAll();
            foreach ($users as $user) {
                $selected = ($user_data && $user_data['user_id'] == $user['user_id']) ? 'selected' : '';
                echo "<option value='{$user['user_id']}' $selected>{$user['full_name']}</option>";
            }
            ?>
        </select>
        <input type="hidden" name="select_user" value="1">
    </form>

    <?php if ($user_data): ?>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user_data['user_id'] ?>">

            <label>الاسم الكامل:</label>
            <input type="text" name="full_name" value="<?= $user_data['full_name'] ?>" required>

            <label>البريد الإلكتروني:</label>
            <input type="email" name="email" value="<?= $user_data['email'] ?>" required>

            <label>الدور:</label>
            <select name="role" required>
                <option value="طالب" <?= $user_data['role'] == 'طالب' ? 'selected' : '' ?>>طالب</option>
                <option value="مشرف" <?= $user_data['role'] == 'مشرف' ? 'selected' : '' ?>>مشرف</option>
                <option value="مدير مشروع" <?= $user_data['role'] == 'مدير مشروع' ? 'selected' : '' ?>>مدير مشروع</option>
            </select>

            <br>
            <button type="submit" name="update_user">تحديث</button>
        </form>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="message success"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="message error"><?= $error ?></p>
    <?php endif; ?>
</div>

</body>
</html>
