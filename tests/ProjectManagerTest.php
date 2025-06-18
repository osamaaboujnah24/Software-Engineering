<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../ProjectManagerd.php';

class ProjectManagerTest extends TestCase {
    public function testDeleteProjectSuccess() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with([5]);
        $stmtMock->method('rowCount')->willReturn(1);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $manager = new ProjectManager($pdoMock);
        $manager->deleteProject(5);

        $this->assertEquals("تم حذف المشروع بنجاح!", $manager->lastMessage);
        $this->assertEquals("dashboard.php", $manager->redirectTo);
    }

    public function testDeleteProjectNotFound() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
                 ->method('execute')
                 ->with([10]);
        $stmtMock->method('rowCount')->willReturn(0);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $manager = new ProjectManager($pdoMock);
        $manager->deleteProject(10);

        $this->assertEquals("حدث خطأ أثناء حذف المشروع.", $manager->lastMessage);
    }

    public function testDeleteProjectException() {
        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')->will($this->throwException(new PDOException("DB Error")));

        $manager = new ProjectManager($pdoMock);
        $manager->deleteProject(7);

        $this->assertStringContainsString("خطأ في قاعدة البيانات", $manager->lastMessage);
    }
}
