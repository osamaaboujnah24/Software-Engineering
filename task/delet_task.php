<?php
include 'data.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

$task_id = $_GET['task_id'] ?? null;

if ($task_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE task_id = ?");
        $stmt->execute([$task_id]);

        $project_id = $_GET['project_id'] ?? null;
        if ($project_id) {
            header("Location: vewtask.php?id=" . $project_id);
            exit;
        } 
        

    } catch (PDOException $e) {
        echo "حدث خطأ أثناء حذف المهمة: " . $e->getMessage();
    }
} else {
    echo "لم يتم تحديد المهمة!";
    exit;
}
?>
