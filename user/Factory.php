<?php
class DashboardFactory {
    public static function redirectByRole($role) {
        switch ($role) {
            case 'مدير مشروع':
                header("Location: dashboardadm.php");
                break;
            case 'طالب':
                header("Location: dashboardST.php");
                break;
            case 'مشرف':
                header("Location: dashboardsupervisor.php");
                break;
            default:
                throw new Exception("  غير معروف.");
        }
        exit;
    }
}
?>
