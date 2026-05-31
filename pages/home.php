<?php
session_start();
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<div class='validation-msg'>
            <img src='../images/error.svg' alt='error icon'>
            <h2 class='validation-text'>Помилка підключення до бази даних</h2>     
          </div>";
    exit;
}

if (isset($_GET['logOut'])) {
    session_destroy(); 
    header("Location: ../index.php");
}

if (isset($_SESSION['EmployeeID'])) {
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link href="https://fonts.cdnfonts.com/css/roboto" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/script.js"></script>
    <title>BookLand - Головна</title>
</head>
<body>
<nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu">
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <div class="navbar-logo">
                    <img src="../images/logo.svg" alt="логотип">
                    <a href="./home.php" id="main">BookLand</a>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="nav navbar-nav navbar-right text-center">
                  <li><a href="./home.php">Головна</a></li>
                  <li><a href="./customers_list.php">Клієнти</a></li>
                  <li><a href="./books_list.php">Книги</a></li>
                  <li><a href="./author_list.php">Автори</a></li> 
                  <li><a href="./employees_list.php">Працівники</a></li>
                  <li><a href="./sales_list.php">Видача книг</a></li>
                  <li><a href="?logOut=1" id="logOut">Вийти</a></li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="main-content" id="main-content">
        <div class="banner col-lg-12">
            <div class="banner-text col-lg-5">
                <h1>Вітаємо у BookLand!</h1>
                <p>BookLand — сучасний книжковий магазин, де кожна книга знаходить свого читача. Ми поєднуємо любов до літератури з комфортним сервісом, щоб ви могли легко знаходити, купувати та відкривати нові історії щодня.</p>
            </div>
        </div>

        <div class="functions col-lg-12">
            <h1>Можливості системи BookLand</h1>
            <div class="col-lg-3 function"><a href="books_list.php"><h2>Книжки</h2><p>Переглядайте широкий вибір книг різних жанрів — від художньої літератури до професійних видань.</p></a></div>
            <div class="col-lg-3 function"><a href="author_list.php"><h2>Автори</h2><p>Дізнавайтеся більше про авторів, чиї книги представлені в магазині. Відкривайте нові імена та слідкуйте за улюбленими письменниками.</p></a></div>
            <div class="col-lg-3 function"><a href="customers_list.php"><h2>Клієнти</h2><p>Керуйте інформацією про покупців магазину, відстежуйте історію замовлень і вподобання клієнтів.</p></a></div>
            <div class="col-lg-3 function"><a href="employees_list.php"><h2>Співробітники</h2><p>Організуйте роботу команди магазину: адміністратори, менеджери та інші працівники, які забезпечують якісний сервіс.</p></a></div>
        </div>

        <div class="functions col-lg-12">
    <h1>Остання статистика</h1>

    <div class="col-lg-12 menu">
        <a href="#" id="booksStatistics" class="col-lg-4">
            Статистика за книгами
        </a>

        <a href="#" id="customersStatistics" class="col-lg-4">
            Статистика за клієнтами
        </a>

        <a href="#" id="employeesStatistics" class="col-lg-4">
            Статистика за працівниками
        </a>
    </div>

    <div class="col-lg-12" id="libraryStatistics">
    </div>
</div>
    </div>
    <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="https://www.facebook.com/?locale=uk_UA">
                <img src="../images/icon_facebook.svg" alt="фейсбук">
            </a>
            <a href="https://www.instagram.com/">
                <img src="../images/icon-instagram.svg" alt="інстаграм">
            </a>
            <a href="https://twitter.com/?lang=uk">
                <img src="../images/icon-twitterx.svg" alt="ікс">
            </a>
        </div>
        <div class="col-lg-3">
            <p>Зв’яжіться з нами: +380-88-675-89-12</p>
        </div>
        <div class="col-lg-12 text-center">
            <p>© 2026 BookLand. Kasianenko A.V. Всі права захищені.</p>
        </div>
    </footer>

</body>
</html>
<?php
} else {
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Помилка - BookLand</title>
</head>
<body>
    <div class="main-content error-msg text-center">
        <img src="../images/error.svg" alt="error icon" style="width: 100px;">
        <div class="error-text">
            <h1>Помилка! Ви не авторизовані</h1>
            <p>Будь ласка, поверніться до <a href="../index.php">сторінки входу</a></p>
        </div>
    </div>
</body>
</html>
<?php
}
?>