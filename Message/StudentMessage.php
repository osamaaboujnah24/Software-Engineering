<?php
session_start();
require_once 'Data.php';

class StudentMessageHandler {
    private $pdo;
    private $userId;
    private $userName;
    private $project;
    public $messages = [];
    public $feedback = '';

    public function __construct($pdo, $userId, $userName) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->loadStudentProject();
    }

    private function loadStudentProject() {
        try {
            $stmt = $this->pdo->prepare("SELECT team_id FROM team_members WHERE user_id = ?");
            $stmt->execute([$this->userId]);
            $team = $stmt->fetch();
            $teamId = $team['team_id'] ?? null;

            if ($teamId) {
                $stmt = $this->pdo->prepare("SELECT project_id, title FROM projects WHERE team_id = ?");
                $stmt->execute([$teamId]);
                $this->project = $stmt->fetch();
            }
        } catch (PDOException $e) {
            error_log("loadStudentProject error: " . $e->getMessage());
            $this->project = null;
        }
    }

    public function getProject() {
        return $this->project;
    }

    public function fetchReceivedMessages() {
        if (!$this->project) return;

        try {
            $stmt = $this->pdo->prepare("
                SELECT m.id, m.message, m.sent_at,
                       sender.full_name AS sender_name, sender.user_id AS sender_id
                FROM messages m
                JOIN users sender ON m.sender_id = sender.user_id
                WHERE m.project_id = ? AND m.receiver_id = ?
                ORDER BY m.sent_at DESC
            ");
            $stmt->execute([$this->project['project_id'], $this->userId]);
            $this->messages = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("fetchReceivedMessages error: " . $e->getMessage());
            $this->messages = [];
        }
    }

    public function sendReply($receiverId, $message) {
        $message = trim($message);
        if (empty($message)) {
            $this->feedback = "<p style='color:red;'> لا يمكن إرسال  .</p>";
            return;
        }

        if (!$this->project) {
            $this->feedback = "<p style='color:red;'> لا يوجد مشروع بك.</p>";
            return;
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO messages (project_id, sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$this->project['project_id'], $this->userId, $receiverId, $message]);
            $this->feedback = "<p style='color:green;'> تم إرسال .</p>";
        } catch (PDOException $e) {
            error_log("sendReply error: " . $e->getMessage());
            $this->feedback = "<p style='color:red;'> حدث خطأ   .</p>";
        }
    }
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::getInstance()->getConnection();
$userId = $_SESSION['user']['user_id'];
$userName = $_SESSION['user']['full_name'];

$handler = new StudentMessageHandler($pdo, $userId, $userName);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_to'], $_POST['reply_message'])) {
    $handler->sendReply($_POST['reply_to'], $_POST['reply_message']);
}

$handler->fetchReceivedMessages();
$project = $handler->getProject();
?>

<h2> رسائل المشروع: <?= htmlspecialchars($project['title'] ?? 'غير موجود') ?></h2>
<?= $handler->feedback ?>

<?php if ($project): ?>
    <?php if (!empty($handler->messages)): ?>
        <?php foreach ($handler->messages as $msg): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <p><strong><?= htmlspecialchars($msg['sender_name']) ?></strong> قال:</p>
                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                <p><em>بتاريخ: <?= $msg['sent_at'] ?></em></p>

                <form method="POST">
                    <input type="hidden" name="reply_to" value="<?= $msg['sender_id'] ?>">
                    <textarea name="reply_message" rows="2" cols="50" placeholder="اكتب ردك ..." required></textarea><br>
                    <button type="submit"> رد</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p> لا توجد رسائل مستلمة.</p>
    <?php endif; ?>
<?php else: ?>
    <p style='color:red;'> لا يوجد مشروع  .</p>
<?php endif; ?>