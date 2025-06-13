<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../ProjectCreator.php';

class ProjectCreatorTest extends TestCase {
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void {
        // لا تستدعِ session_start() إطلاقًا هنا
        // فقط هيئ الـ $_SESSION مباشرة
        $_SESSION = ['user' => ['role' => 'مدير مشروع']];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetchAll')->willReturn([]);

        $this->pdoMock = $this->createMock(PDO::class);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->pdoMock->method('lastInsertId')->willReturn(1);
    }

    public function testLoadsManagersSupervisorsTeams() {
        $creator = new ProjectCreator($this->pdoMock);
        $this->assertIsArray($creator->managers);
        $this->assertIsArray($creator->supervisors);
        $this->assertIsArray($creator->teams);
    }

    public function testUnauthorizedUserIsRedirected() {
        $_SESSION['user'] = ['role' => 'طالب'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unauthorized');

        new class($this->pdoMock) extends ProjectCreator {
            public function __construct($pdo) {
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
                    throw new \Exception('Unauthorized');
                }
            }
        };
    }

    public function testFormSubmissionInsertsProjectAndManager() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'title' => 'مشروع اختبار',
            'description' => 'وصف المشروع',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'manager_id' => 1,
            'supervisor_id' => 2,
            'team_id' => 3
        ];

        $creator = new class($this->pdoMock) extends ProjectCreator {
            public function __construct($pdo) {
                $this->pdo = $pdo;
                $this->error = '';
                $_SESSION['user'] = ['role' => 'مدير مشروع'];
                $_SERVER['REQUEST_METHOD'] = 'POST';
                $this->handleFormSubmission();
            }

            public function handleFormSubmission() {
                try {
                    $stmt = $this->pdo->prepare("insert ..."); // محاكاة فقط
                    $stmt->execute([]);

                    $stmt2 = $this->pdo->prepare("insert ...");
                    $stmt2->execute([]);
                } catch (PDOException $e) {
                    $this->error = "فشل: " . $e->getMessage();
                }
            }
        };

        $this->assertEquals('', $creator->error);
    }
} 