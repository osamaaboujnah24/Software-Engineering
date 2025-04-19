<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #1e272e, #2f3640);
            color: #ffffff;
            text-align: center;
        }

        header {
            background-color: #00a8ff;
            padding: 50px 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        header h1 {
            font-size: 40px;
            margin: 0;
        }

        nav {
            background-color: #0097e6;
            padding: 15px 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            margin: 0 10px;
            font-size: 18px;
            border-radius: 5px;
            transition: 0.3s;
        }

        nav a:hover {
            background-color: #2ecc71
;
            transform: scale(1.1);
        }

        .hero {
            padding: 80px 20px;
            background-image: url('https://images.unsplash.com/photo-1531496651457-85649b52fac6');
            background-size: cover;
            background-position: center;
            position: relative;
            color: white;
        }

        .hero h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 60px 20px;
        }

        .feature-box {
            background-color: #353b48;
            border-radius: 12px;
            margin: 20px;
            padding: 30px;
            width: 250px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            transition: 0.3s;
        }

        .feature-box:hover {
            transform: scale(1.05);
        }

    </style>
</head>
<body>
    <header>
        <h1> إدارة مشاريع الطلاب</h1>
    </header>

    <nav>
        <a href="login.php">تسجيل الدخول</a>
        <a href="register.php">تسجيل جديد</a>
    </nav>

    <section class="hero">
        <h2>أهلاً بك </h2>
        <p>نحن نساعدك على تنظيم، متابعة، والتعاون في مشاريعك الجامعية بكل سهولة وسلاسة..</p>
    </section>

    <section class="features">
        <div class="feature-box">
            <h3>إدارة المهام</h3>
            <p>أنشئ، تتبع، ووزّع المهام بين أعضاء الفريق بكل سهولة.</p>
        </div>
        <div class="feature-box">
            <h3>تقارير فورية</h3>
        </div>
        <div class="feature-box">
            <h3>إشعارات وتنبيهات</h3>
            <p>لا تفوّت أي موعد أو تحديث، تصلك التنبيهات في وقتها.</p>
        </div>
        <div class="feature-box">
            <h3>مشاركة الملفات</h3>
            <p>ارفع وشارك مستندات المشروع بسهولة بين الفريق والمشرف.</p>
        </div>
    </section>

</body>
</html>
