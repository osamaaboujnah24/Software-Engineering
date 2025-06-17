<?php
class DashboardFactory {
    public static string $redirectUrl = ''; 

    public static function redirectByRole($role, $testMode = false) {
        switch ($role) {
            case 'مدير مشروع':
                $url = 'dashboardadm.php';
                break;
            case 'طالب':
                $url = 'dashboardST.php';
                break;
            case 'مشرف':
                $url = 'dashboardsupervisor.php';
                break;
            default:
                throw new Exception("نوع المستخدم غير معروف.");
        }

        if ($testMode) {
            self::$redirectUrl = $url;  
        } else {
            header("Location: $url");
            exit;
        }
    }
}
