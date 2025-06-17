<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../deleteTask.php';

class TaskDeleterTest extends TestCase {
    public function testDeleteTaskSuccess() {
        $pdoMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetch')->willReturn(['assigned_user_id' => 1, 'title' => 'مهمة اختبار']);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $taskDeleter = new TaskDeleter($pdoMock);
        $result = $taskDeleter->deleteTask(5, 'مدير مشروع');

        $this->assertEquals("تم الحذف بنجاح", $result);
    }

    public function testDeleteTaskWithoutId() {
        $pdoMock = $this->createMock(PDO::class);
        $taskDeleter = new TaskDeleter($pdoMock);

        $result = $taskDeleter->deleteTask(null, 'مدير مشروع');
        $this->assertEquals("لم يتم تحديد المهمة!", $result);
    }

    public function testUnauthorizedAccess() {
        $pdoMock = $this->createMock(PDO::class);
        $taskDeleter = new TaskDeleter($pdoMock);

        $result = $taskDeleter->deleteTask(5, 'طالب');
        $this->assertEquals("unauthorized", $result);
    }
}
