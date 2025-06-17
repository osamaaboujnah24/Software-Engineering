<?php
class TaskDeleter {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function deleteTask($task_id, $userRole = null) {
        if ($userRole !== 'مدير مشروع') {
            return 'unauthorized';
        }

        if (!$task_id) {
            return "لم يتم تحديد المهمة!";
        }

        try {
            $stmt = $this->pdo->prepare("SELECT assigned_user_id, title FROM tasks WHERE task_id = ?");
            $stmt->execute([$task_id]);
            $task = $stmt->fetch();

            if ($task) {
                $notificationMsg = " تم حذف المهمة '{$task['title']}' التي كُلفت بها.";
                $stmtNotif = $this->pdo->prepare("INSERT INTO notifications (user_id, content) VALUES (?, ?)");
                $stmtNotif->execute([$task['assigned_user_id'], $notificationMsg]);
            }

            $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE task_id = ?");
            $stmt->execute([$task_id]);

            return "تم الحذف بنجاح";
        } catch (PDOException $e) {
            return "خطأ: " . $e->getMessage();
        }
    }
}
