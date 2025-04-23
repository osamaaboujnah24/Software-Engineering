<?php
include 'database.php';
session_start();

class ReportHandler {
    private $pdo;
    private $project_id;
    private $project;
    private $user_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->validateSession();
        $this->user_id = $_SESSION['user']['user_id'];
        $this->validateProjectId();
        $this->fetchProject();
        $this->handleFormSubmission();
    }

    private function validateSession() {
        if (!isset($_SESSION['user'])) {
            header("Location: login.php");
            exit;
        }
    }

    private function validateProjectId() {
        if (!isset($_GET['project_id'])) {
            exit("المشروع غير موجود!");
        }
        $this->project_id = $_GET['project_id'];
    }

    private function fetchProject() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
            $stmt->execute([$this->project_id]);
            $this->project = $stmt->fetch();

            if (!$this->project) {
                exit("المشروع غير موجود!");
            }
        } catch (PDOException $e) {
            exit("خطأ في جلب المشروع: " . $e->getMessage());
        }
    }

    private function handleFormSubmission() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $title = $_POST['report_title'];
                $content = $_POST['report_content'];

                $stmt = $this->pdo->prepare("INSERT INTO reports (project_id, title, content, user_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$this->project_id, $title, $content, $this->user_id]);

                echo "<script>alert('تم إضافة التقرير بنجاح!'); window.location.href='write_report.php?project_id={$this->project_id}';</script>";
                exit;
            } catch (PDOException $e) {
                exit("حدث خطأ أثناء إرسال التقرير: " . $e->getMessage());
            }
        }
    }

    public function getProject() {
        return $this->project;
    }
}

$reportHandler = new ReportHandler($pdo);
$project = $reportHandler->getProject();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كتابة تقرير</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #34495e;
            color: #ecf0f1;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            flex-direction: column;
        }

        header {
            background-color: #1abc9c;
            color: white;
            padding: 20px 0;
            width: 100%;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        header h2 {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
        }

        form {
            background-color: #2c3e50;
            width: 400px;
            margin-top: 30px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        form:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            text-align: right;
            margin: 12px 0 8px;
            font-size: 18px;
            font-weight: 500;
            color: #e74c3c;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus, textarea:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 8px rgba(46, 204, 113, 0.3);
            outline: none;
        }

        textarea {
            resize: vertical;
            height: 150px;
        }

        button {
            padding: 16px 32px;
            background-color: #16a085;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 20px;
        }

        button:hover {
            background-color: #1abc9c;
            transform: translateY(-2px);
        }

        .logout-link {
            margin-top: 30px;
            display: inline-block;
            padding: 12px 25px;
            background-color: #f39c12;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .logout-link:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<header>
    <h2>كتابة تقرير للمشروع: <?php echo htmlspecialchars($project['title']); ?></h2>
</header>

<form method="POST">
    <label for="report_title">عنوان التقرير:</label>
    <input type="text" name="report_title" required><br>

    <label for="report_content">محتوى التقرير:</label>
    <textarea name="report_content" required></textarea><br>

    <button type="submit">إرسال التقرير</button>
</form>

</body>
</html>
