<?php
session_start();
require_once 'Data.php';

class MessageManager {
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getManagedProjects() {
        $stmt = $this->pdo->prepare("SELECT project_id, title FROM projects WHERE manager_id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    public function sendMessageToTeam($projectId, $message) {
        $stmt = $this->pdo->prepare("
            SELECT u.user_id
            FROM users u
            JOIN team_members tm ON u.user_id = tm.user_id
            JOIN projects p ON p.team_id = tm.team_id
            WHERE p.project_id = ? AND u.user_id != ?
        ");
        $stmt->execute([$projectId, $this->userId]);
        $receivers = $stmt->fetchAll();

        if (!empty($receivers) && !empty($message)) {
            $stmt = $this->pdo->prepare("INSERT INTO messages (project_id, sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, ?, NOW())");
            foreach ($receivers as $receiver) {
                $stmt->execute([$projectId, $this->userId, $receiver['user_id'], $message]);
            }
            return " تم إرسال الرسالة إلى كل الأعضاء.";
        }
        return " لا يمكن إرسال رسالة فارغة أو لا يوجد أعضاء.";
    }

    public function getSentMessages($projectIds) {
        $inClause = rtrim(str_repeat('?,', count($projectIds)), ',');
        $stmt = $this->pdo->prepare("
            SELECT m.message, m.sent_at, sender.full_name AS sender_name,
                   receiver.full_name AS receiver_name, p.title AS project_title
            FROM messages m
            JOIN users sender ON m.sender_id = sender.user_id
            JOIN users receiver ON m.receiver_id = receiver.user_id
            JOIN projects p ON p.project_id = m.project_id
            WHERE m.project_id IN ($inClause) AND m.sender_id = ?
            ORDER BY m.sent_at DESC
        ");
        $stmt->execute([...$projectIds, $this->userId]);
        return $stmt->fetchAll();
    }

    public function getReceivedMessages($projectIds) {
        $inClause = rtrim(str_repeat('?,', count($projectIds)), ',');
        $stmt = $this->pdo->prepare("
            SELECT m.message, m.sent_at, sender.full_name AS sender_name,
                   receiver.full_name AS receiver_name, p.title AS project_title
            FROM messages m
            JOIN users sender ON m.sender_id = sender.user_id
            JOIN users receiver ON m.receiver_id = receiver.user_id
            JOIN projects p ON p.project_id = m.project_id
            WHERE m.project_id IN ($inClause) AND m.receiver_id = ?
            ORDER BY m.sent_at DESC
        ");
        $stmt->execute([...$projectIds, $this->userId]);
        return $stmt->fetchAll();
    }
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::getInstance()->getConnection();
$userId = $_SESSION['user']['user_id'];
$userName = $_SESSION['user']['full_name'];

$manager = new MessageManager($pdo, $userId);
$projects = $manager->getManagedProjects();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['project_id'])) {
    $result = $manager->sendMessageToTeam($_POST['project_id'], trim($_POST['message']));
    echo "<p style='color:" . (str_starts_with($result, '/') ? "green" : "red") . ";'>$result</p>";
}
?>

<h2> إرسال رسالة لكل أعضاء المشروع</h2>

<?php if (!empty($projects)): ?>
    <form method="POST">
        <label> اختر المشروع:</label>
        <select name="project_id" required>
            <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj['project_id'] ?>"><?= htmlspecialchars($proj['title']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>الرسالة:</label><br>
        <textarea name="message" rows="4" cols="50" required></textarea><br><br>

        <button type="submit"> إرسال للجميع</button>
    </form>
<?php else: ?>
    <p style="color:red;"> لا يوجد مشروع أنت مدير له حاليًا.</p>
<?php endif; ?>

<hr>
<h2> الرسائل التي أرسلتها</h2>
<?php
$projectIds = array_column($projects, 'project_id');
if (!empty($projectIds)) {
    $sentMessages = $manager->getSentMessages($projectIds);
    if ($sentMessages) {
        foreach ($sentMessages as $msg) {
            echo "<p><strong>" . htmlspecialchars($msg['sender_name']) . "</strong> إلى <strong>" . 
                htmlspecialchars($msg['receiver_name']) . "</strong> (" .
                htmlspecialchars($msg['project_title']) . "): " .
                htmlspecialchars($msg['message']) . 
                " <em>(" . $msg['sent_at'] . ")</em></p>";
        }
    } else {
        echo "<p> لا توجد رسائل مرسلة.</p>";
    }

    echo "<hr><h2> الرسائل التي استلمتها</h2>";

    $receivedMessages = $manager->getReceivedMessages($projectIds);
    if ($receivedMessages) {
        foreach ($receivedMessages as $msg) {
            echo "<p><strong>" . htmlspecialchars($msg['sender_name']) .
                "</strong> قال لك   <strong>" .
                htmlspecialchars($msg['project_title']) . "</strong>: " .
                htmlspecialchars($msg['message']) .
                " <em>(" . $msg['sent_at'] . ")</em></p>";
        }
    } else {
        echo "<p> لا توجد رسائل مستلمة.</p>";
    }
}
?>
