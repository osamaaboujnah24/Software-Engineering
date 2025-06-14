<?php

use PHPUnit\Framework\TestCase;
require_once 'TaskManager.php';  // تأكد من المسار الصحيح


class AddTaskTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;
    private $taskManager;

    protected function setUp(): void
    {
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $_SESSION['user'] = ['role' => 'مدير مشروع', 'user_id' => 1];

        $this->taskManager = $this->getMockBuilder(TaskManager::class)
            ->setConstructorArgs([$this->pdoMock])
            ->onlyMethods(['loadUsers', 'loadProjects'])
            ->getMock();

        $this->taskManager->method('loadUsers')->willReturn([
            ['user_id' => 1, 'full_name' => 'محمد علي'],
            ['user_id' => 2, 'full_name' => 'علي حسن']
        ]);
        $this->taskManager->method('loadProjects')->willReturn([
            ['project_id' => 1, 'title' => 'مشروع 1'],
            ['project_id' => 2, 'title' => 'مشروع 2']
        ]);

        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    public function testAddTaskSuccess()
    {
        $this->stmtMock->method('execute')->willReturn(true);

        $_POST = [
            'title' => 'مهمة جديدة',
            'description' => 'وصف المهمة',
            'assigned_to' => 2,
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-10',
            'due_date' => '2025-06-10',
            'status' => 'معلقة',
            'project_id' => 1
        ];

        $this->taskManager->handleTaskCreation();

        $this->expectOutputRegex('/manage_tasks.php/');
    }

    public function testUnauthorizedUser()
    {
        $_SESSION['user'] = ['role' => 'طالب', 'user_id' => 2];

        $_POST = [
            'title' => 'مهمة جديدة',
            'description' => 'وصف المهمة',
            'assigned_to' => 2,
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-10',
            'due_date' => '2025-06-10',
            'status' => 'معلقة',
            'project_id' => 1
        ];

        $this->expectException(\PHPUnit\Framework\Error\Warning::class);
        $this->expectExceptionMessage("Session not started");

        $this->taskManager->handleTaskCreation();
    }
}
?>