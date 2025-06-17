<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../LoginHandler.php';
require_once __DIR__ . '/../Factory.php';

class LoginHandlerTest extends TestCase {
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = [];  // إعادة تعيين الجلسة

        // تحضير البيانات الافتراضية
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';

        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn([
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'مدير مشروع'
        ]);

        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
    }

    public function testSuccessfulLogin() {
        $handler = new LoginHandler($this->pdoMock, true);  // وضع الاختبار
        $handler->processLogin();

        $this->assertEquals('dashboardadm.php', DashboardFactory::$redirectUrl);
    }

    public function testInvalidLogin() {
        $_POST['password'] = 'wrongpassword';  // كلمة مرور خاطئة

        $this->stmtMock->method('fetch')->willReturn([
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'مدير مشروع'
        ]);

        $handler = new LoginHandler($this->pdoMock, true);
        $handler->processLogin();

        $this->assertEquals('بيانات الدخول غير صحيحة.', $handler->getError());
    }

    public function testEmptyInput() {
        $_POST['email'] = '';
        $_POST['password'] = '';

        $this->stmtMock->method('fetch')->willReturn(false);

        $handler = new LoginHandler($this->pdoMock, true);
        $handler->processLogin();

        $this->assertEquals('بيانات الدخول غير صحيحة.', $handler->getError());
    }
}
