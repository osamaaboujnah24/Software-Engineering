<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'مدير مشروع') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة إدارة الفرق</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            background-color: #f1f1f1;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #343a40;
            overflow: hidden;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            font-weight: bold;
        }

        .navbar a:hover {
            background-color: #495057;
            border-radius: 5px;
        }

        h1 {
            margin: 40px 0 20px;
            text-align: center;
            color: #333;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: scale(1.03);
        }

        .card a {
            text-decoration: none;
            color: white;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>
        <a href="tes.php">إدارة الفرق</a>
        <a href="project_dashboard.php">إدارة المشاريع</a>
        <a href="up.php">تعديل بيانات المستخدمين</a>
    </div>
    <div>
        <a href="logout.php">تسجيل الخروج</a>
    </div>
</div>

<h1>لوحة إدارة الفرق</h1>

<div class="dashboard">

    <div class="card">
        <h3>إنشاء فريق جديد</h3>
        <a href="cty.php">إنشاء</a>
    </div>

    <div class="card">
        <h3>عرض أعضاء الفرق</h3>
        <a href="veteam.php">عرض</a>
    </div>

    <div class="card">
        <h3>إضافة عضو إلى فريق</h3>
        <a href="team.php"> إضافة</a>
    </div>

    <div class="card">
        <h3>حذف عضو من فريق</h3>
        <a href="reteam.php"> حذف</a>
    </div>


</div>

</body>
</html>
