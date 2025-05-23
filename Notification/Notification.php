<?php
session_start();
include 'data.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

class NotificationHandler {
    private $pdo;
    private $userId;
    public $notifications = [];

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function fetchNotifications() {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$this->userId]);
        $this->notifications = $stmt->fetchAll();
    }

    public function markAllAsRead() {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        header("Location: notifications.php");
        exit();
    }
}

// استخدام الكلاس
$userId = $_SESSION['user']['user_id'];
$notifHandler = new NotificationHandler($pdo, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notifHandler->markAllAsRead();
}

$notifHandler->fetchNotifications();
$notifications = $notifHandler->notifications;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إشعاراتي</title>
    <style>
        body { font-family: Arial, sans-serif;
		background: #f8f9fa;
		padding: 20px;
		}
        h2 { text-align: center;
		color: #333; 
		}
        ul { list-style-type: none;
		padding: 0;
		}
        li { padding: 10px;
		border-bottom: 1px solid #ccc;
		}
        form { text-align: center;
		margin-top: 20px;
		}
        button { padding: 10px 20px;
		background: #007bff;
		color: white;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		}
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<h2> إشعاراتك</h2>

<?php if (!empty($notifications)): ?>
    <ul>
        <?php foreach ($notifications as $note): ?>
            <li style="<?= $note['is_read'] ? 'color:gray;' : 'font-weight:bold;' ?>">
                <?= htmlspecialchars($note['content']) ?>
                <em>(<?= $note['created_at'] ?>)</em>
            </li>
        <?php endforeach; ?>
    </ul>
    <form method="POST">
        <button type="submit" name="mark_read">تعليم الكل كمقروء</button>
    </form>
<?php else: ?>
    <p style="text-align: center;">لا توجد إشعارات.</p>
<?php endif; ?>

</body>
</html>
