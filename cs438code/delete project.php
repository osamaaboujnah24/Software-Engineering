<?php
include 'database.php';
session_start();

// التحقق من إذا كان المستخدم هو مدير مشروع
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'مدير مشروع') {
    header("Location: login.php");
    exit;
}

// التحقق من وجود `project_id` في الرابط
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    // تنفيذ استعلام لحذف المشروع من جدول projects
    $stmt = $pdo->prepare("DELETE FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);

    // التحقق من نجاح عملية الحذف
    if ($stmt->rowCount() > 0) {
        // إذا تم الحذف بنجاح، نعرض رسالة بنجاح الحذف
        echo "<script>alert('تم حذف المشروع بنجاح!'); window.location.href = 'dashboard.php';</script>";
        exit;
    } else {
        // إذا لم يتم الحذف (مثلاً إذا لم يتم العثور على المشروع)
        echo "<script>alert('حدث خطأ أثناء حذف المشروع.'); window.location.href = 'dashboard.php';</script>";
        exit;
    }
} else {
    // إذا كانت `project_id` غير موجودة في الرابط
    echo "<script>alert('المشروع غير موجود!'); window.location.href = 'dashboard.php';</script>";
    exit;
}
?>
