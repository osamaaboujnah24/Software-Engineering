<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../NotificationHandler.php';

class NotificationHandlerTest extends TestCase {
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
    }

    public function testFetchNotifications() {
        $expectedNotifications = [
            ['content' => 'رسالة 1', 'created_at' => '2024-01-01', 'is_read' => 0],
            ['content' => 'رسالة 2', 'created_at' => '2024-01-02', 'is_read' => 1],
        ];

        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn($expectedNotifications);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $handler = new NotificationHandler($this->pdoMock, 1);
        $handler->fetchNotifications();

        $this->assertEquals($expectedNotifications, $handler->notifications);
    }

    public function testMarkAllAsRead() {
        $this->stmtMock->expects($this->once())
                       ->method('execute')
                       ->with([5]);

        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $handler = new NotificationHandler($this->pdoMock, 5);
        $handler->markAllAsRead(false); 
        $this->assertTrue(true); 
    }
}
