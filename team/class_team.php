<?php

class TeamManagement {
    private $pdo;
    public $error = '';
    public $success = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addMemberToTeam($user_name, $team_id) {
        try {
            $stmt_user = $this->pdo->prepare("SELECT user_id FROM users WHERE full_name = ?");
            $stmt_user->execute([$user_name]);
            $user = $stmt_user->fetch();

            if (!$user) {
                $this->error = " العضو غير موجود.";
                return;
            }

            $check = $this->pdo->prepare("SELECT * FROM team_members WHERE team_id = ? AND user_id = ?");
            $check->execute([$team_id, $user['user_id']]);
            if ($check->rowCount() > 0) {
                $this->error = " العضو موجود بالفعل.";
                return;
            }

            $stmt_add = $this->pdo->prepare("INSERT INTO team_members (team_id, user_id) VALUES (?, ?)");
            $stmt_add->execute([$team_id, $user['user_id']]);
            $this->success = " تم إضافة '$user_name' بنجاح.";
        } catch (PDOException $e) {
            $this->error = " خطأ: " . $e->getMessage();
        }
    }

    public function removeMemberFromTeam($team_id, $user_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
            $stmt->execute([$team_id, $user_id]);

            if ($stmt->rowCount() > 0) {
                $this->success = " تم الحذف بنجاح.";
            } else {
                $this->error = " العضو غير موجود في الفريق.";
            }
        } catch (PDOException $e) {
            $this->error = " خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    }
}
