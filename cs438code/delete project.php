<?php
session_start();
include 'database.php';

class ProjectManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function deleteProject($project_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM projects WHERE project_id = ?");
            $stmt->execute([$project_id]);

            if ($stmt->rowCount() > 0) {
                $this->redirectWithMessage("تم حذف المشروع بنجاح!", "dashboard.php");
            } else {
                $this->redirectWithMessage("حدث خطأ أثناء حذف المشروع.", "dashboard.php");
            }
        } catch (PDOException $e) {
            $this->redirectWithMessage("خطأ في قاعدة البيانات: " . $e->getMessage(), "dashboard.php");
        }
    }

    private function redirectWithMessage($message, $redirectTo) {
        echo "<script>alert('$message'); window.location.href = '$redirectTo';</script>";
        exit;
    }
}

// التحقق من تسجيل دخول المستخدم وصلاحيته
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

// التحقق من وجود project_id
if (isset($_GET['id'])) {
    $manager = new ProjectManager($pdo);
    $manager->deleteProject($_GET['id']);
} else {
    echo "<script>alert('المشروع غير موجود!'); window.location.href = 'dashboard.php';</script>";
    exit;
}
