<?php
class AuthManager {
    public static function requireRole($role) {
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
            header("Location: login.php");
            exit;
        }
    }
}
