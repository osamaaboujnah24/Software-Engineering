<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../team_management_logic.php'; 

class TeamManagementTest extends TestCase {
    private $pdoMock;

    protected function setUp(): void {
        $this->pdoMock = $this->createMock(PDO::class);
    }

    public function testAddNonexistentUser() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $manager = new TeamManagement($this->pdoMock);
        $manager->addMemberToTeam('غير موجود', 4);

        $this->assertEquals(" العضو غير موجود.", $manager->error);
    }

    public function testAddMemberAlreadyExistsInTeam() {
        $stmtMock = $this->createMock(PDOStatement::class);

        $stmtMock->method('fetch')->willReturn(['user_id' => 1]); 
        $stmtMock->method('rowCount')->willReturn(1);  

        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $manager = new TeamManagement($this->pdoMock);
        $manager->addMemberToTeam('محمد أحمد', 2);

        $this->assertEquals(" العضو موجود بالفعل.", $manager->error);
    }

    public function testRemoveNonexistentMemberFromTeam() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('rowCount')->willReturn(0);  

        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $manager = new TeamManagement($this->pdoMock);
        $manager->removeMemberFromTeam(2, 7);

        $this->assertEquals(" العضو غير موجود في الفريق.", $manager->error);
    }
}
