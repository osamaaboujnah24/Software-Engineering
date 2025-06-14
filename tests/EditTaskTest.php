<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../EditTaskHandler.php';

class EditTaskHandlerTest extends TestCase {

    protected function setUp(): void {
        $_POST = [
            'title' => 'عنوان تجريبي',
            'description' => 'وصف المهمة',
            'assigned_user_id' => '2',
            'start_date' => '2025-06-15',
            'end_date' => '2025-06-30',
            'status' => 'معلقة'
        ];
    }

    // ✅ 1. اختبار getFormInput
    public function testGetFormInputReturnsCorrectValues() {
        $pdoMock = $this->createMock(PDO::class);
        $handler = new EditTaskHandler($pdoMock, 1, 1);

        $method = new ReflectionMethod(EditTaskHandler::class, 'getFormInput');
        $method->setAccessible(true);
        $result = $method->invoke($handler);

        $this->assertEquals('عنوان تجريبي', $result[0]);
        $this->assertEquals('وصف المهمة', $result[1]);
        $this->assertEquals('2', $result[2]);
        $this->assertEquals('2025-06-15', $result[3]);
        $this->assertEquals('2025-06-30', $result[4]);
        $this->assertEquals('معلقة', $result[5]);
    }

    // ✅ 2. اختبار updateTaskInDatabase
    public function testUpdateTaskInDatabaseCallsPrepareAndExecute() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())->method('execute');

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $handler = new EditTaskHandler($pdoMock, 1, 1);

        $method = new ReflectionMethod(EditTaskHandler::class, 'updateTaskInDatabase');
        $method->setAccessible(true);

        $method->invoke($handler, 'عنوان', 'وصف', 2, '2025-06-01', '2025-06-15', 'معلقة');

        $this->assertTrue(true);
    }

    // ✅ 3. اختبار notifyUser
    public function testNotifyUserInsertsNotification() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($params) {
                return $params[0] === 2 && strpos($params[1], 'عنوان المهمة') !== false;
            }));

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $handler = new EditTaskHandler($pdoMock, 1, 1);

        $method = new ReflectionMethod(EditTaskHandler::class, 'notifyUser');
        $method->setAccessible(true);

        $method->invoke($handler, 2, 'عنوان المهمة');

        $this->assertTrue(true);
    }

    // ✅ 4. اختبار loadUsers
    public function testLoadUsersFetchesUsers() {
        $expectedUsers = [
            ['user_id' => 1, 'full_name' => 'أحمد'],
            ['user_id' => 2, 'full_name' => 'ليلى']
        ];

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchAll')->willReturn($expectedUsers);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')->willReturn($stmtMock);

        $handler = new EditTaskHandler($pdoMock, 1, 1);
        $handler->loadUsers();

        $this->assertEquals($expectedUsers, $handler->users);
    }

    // ✅ 5. اختبار loadTask بدون ID
    public function testLoadTaskWithoutIdSetsError() {
        $pdoMock = $this->createMock(PDO::class);
        $handler = new EditTaskHandler($pdoMock, null, 1);

        $result = $handler->loadTask();

        $this->assertFalse($result);
        $this->assertEquals('لم يتم تحديد المهمة.', $handler->error);
    }
}
