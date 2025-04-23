<?php
session_start();
require_once 'database.php';

class ProjectReportPage {
    private $pdo;
    private $projects = [];
    private $error_message = '';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->authorizeUser();
        $this->fetchProjects();
        $this->render();
    }

    private function authorizeUser() {
        if (!isset($_SESSION['user'])) {
            header("Location: login.php");
            exit;
        }
    }

    private function fetchProjects() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM projects");
            $stmt->execute();
            $this->projects = $stmt->fetchAll();

            if (empty($this->projects)) {
                $this->error_message = "لا توجد مشاريع حالياً لكتابة تقارير.";
            }
        } catch (Exception $e) {
            $this->error_message = "حدث خطأ أثناء جلب المشاريع: " . $e->getMessage();
        }
    }

    private function render() {
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>كتابة تقرير للمشاريع</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, sans-serif;
                    background: linear-gradient(135deg, #1d2b2f, #304d4d);
                    color: #ecf0f1;
                    margin: 0;
                    padding: 0;
                    text-align: center;
                }
                header {
                    background-color: #27ae60;
                    padding: 25px 0;
                    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
                }
                header h2 {
                    margin: 0;
                    font-size: 28px;
                    color: white;
                }
                .container {
                    width: 90%;
                    max-width: 1100px;
                    margin: 40px auto;
                    background-color: #2c3e50;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    padding: 14px;
                    text-align: center;
                    border: 1px solid #34495e;
                }
                th {
                    background-color: #27ae60;
                    color: white;
                }
                td {
                    background-color: #34495e;
                    color: #ecf0f1;
                }
                tr:hover td {
                    background-color: #3b5d5d;
                }
                .write-btn {
                    background-color: #e67e22;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: bold;
                    transition: background-color 0.3s ease, transform 0.3s ease;
                }
                .write-btn:hover {
                    background-color: #d35400;
                    transform: scale(1.05);
                }
                .error-message {
                    color: #e74c3c;
                    font-size: 18px;
                    margin-top: 20px;
                }
                .back-link {
                    display: inline-block;
                    margin-top: 30px;
                    background-color: #16a085;
                    padding: 12px 25px;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                }
                .back-link:hover {
                    background-color: #138d75;
                }
            </style>
        </head>
        <body>
        <header>
            <h2>كتابة تقرير لمشروع</h2>
        </header>
        <div class="container">
            <?php if ($this->error_message): ?>
                <p class="error-message"><?php echo $this->error_message; ?></p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>اسم المشروع</th>
                        <th>الوصف</th>
                        <th>الفترة</th>
                        <th>إجراء</th>
                    </tr>
                    <?php foreach ($this->projects as $project): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['title']); ?></td>
                            <td><?php echo htmlspecialchars($project['description']); ?></td>
                            <td><?php echo htmlspecialchars($project['start_date']) . " إلى " . htmlspecialchars($project['end_date']); ?></td>
                            <td>
                                <a class="write-btn" href="report2.php?project_id=<?php echo $project['project_id']; ?>">كتابة تقرير</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            <a href="dashboardadm.php" class="back-link">العودة إلى لوحة التحكم</a>
        </div>
        </body>
        </html>
        <?php
    }
}

new ProjectReportPage($pdo);
