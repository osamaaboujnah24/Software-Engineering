<?php
session_start();
require_once 'Factory.php';

class LoginHandler {
    private $pdo;
    private $error;
    private $testMode = false;

    public function __construct($pdo, $testMode = false) {
        $this->pdo = $pdo;
        $this->testMode = $testMode;
    }

    public function processLogin() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            try {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && $password === $user['password']) {
                    $_SESSION['user'] = $user;
                    $this->redirectUser($user['role']);
                } else {
                    $this->error = "بيانات الدخول غير صحيحة.";
                }
            } catch (PDOException $e) {
                $this->error = "خطأ في قاعدة البيانات: " . $e->getMessage();
            }
        }
    }

    private function redirectUser($role) {
        DashboardFactory::redirectByRole($role, $this->testMode);
    }

    public function getError() {
        return $this->error;
    }
}
