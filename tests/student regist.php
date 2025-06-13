<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../StudentRegistration.php';

class StudentRegistrationTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;
    private $registration;

    protected function setUp(): void
    {
        // إعداد محاكاة لكائن PDOStatement
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->stmtMock->method('execute')->willReturn(true);

        // إعداد محاكاة لكائن PDO
        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        // إنشاء كائن StudentRegistration مع PDO المحاكي
        $this->registration = new StudentRegistration($this->pdoMock);
        $_SESSION = []; // مسح الجلسة قبل كل اختبار
    }

    public function testSuccessfulRegistration()
    {
        $result = $this->registration->register('Test User', 'test@example.com', 'secret');
        $this->assertTrue($result);
        $this->assertEmpty($this->registration->error);
        $this->assertArrayHasKey('user', $_SESSION);
        $this->assertEquals('Test User', $_SESSION['user']['full_name']);
    }

    public function testRegistrationExceptionSetsError()
    {
        $this->pdoMock->method('prepare')->will($this->throwException(new PDOException("DB error")));

        $registrationWithError = new StudentRegistration($this->pdoMock);
        $result = $registrationWithError->register('User', 'err@example.com', 'pass');

        $this->assertFalse($result);
        $this->assertStringContainsString("حدث خطأ أثناء التسجيل", $registrationWithError->error);
    }

    public function testEmptyNameRegistration()
    {
        $result = $this->registration->register('', 'email@example.com', '1234');
        $this->assertTrue($result); // لا يوجد تحقق من الحقول في الكلاس حالياً
        $this->assertEquals('', $_SESSION['user']['full_name']);
    }

    public function testEmptyEmailRegistration()
    {
        $result = $this->registration->register('Name', '', '1234');
        $this->assertTrue($result); // لا يوجد تحقق من البريد
        $this->assertEquals('Name', $_SESSION['user']['full_name']);
    }

    public function testEmptyPasswordRegistration()
    {
        $result = $this->registration->register('Name', 'email@example.com', '');
        $this->assertTrue($result); // الكلمة فارغة ولكن يتم القبول في الكود الحالي
        $this->assertEquals('Name', $_SESSION['user']['full_name']);
    }

    public function testSessionDataAfterRegistration()
    {
        $this->registration->register('Ali', 'ali@example.com', '123');
        $this->assertEquals([
            'full_name' => 'Ali',
            'role' => 'طالب'
        ], $_SESSION['user']);
    }
}
