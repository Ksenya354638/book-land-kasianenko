<?php
session_start();

// Отримання параметрів з оточення
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '3306'; 
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

$conn = null;
$db_error = false;

try {
    // Додаємо порт у DSN для стабільної роботи на Render
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 7,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    $db_error = true;
}

if(isset($_SESSION['LibrarianID'])) {
    $success = false;
    
    if(isset($_POST['add-customer'])){
        // Перевіряємо, чи підключення успішне, перед запитом
        if ($conn) {
            try {
                $query = $conn->prepare("INSERT INTO customers (FirstName, ParentalName, Surname, Address, PhoneNumber, Employment) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
                $query->execute([
                    $_POST['fname'], 
                    $_POST['pname'], 
                    $_POST['sname'], 
                    $_POST['address'], 
                    $_POST['phone'], 
                    $_POST['employment']
                ]);
                $success = true;
            } catch (PDOException $e) {
                $db_error = true;
            }
        } else {
            $db_error = true;
        }
    }

    if (isset($_GET['logOut'])){
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link href="https://fonts.cdnfonts.com/css/roboto" rel="stylesheet">
    <title>Реєстрація читача | LibraVerse</title>
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
                    <a href="./home.php" id="main">LibraVerse</a>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="nav navbar-nav navbar-right text-center">
                  <li><a href="./home.php">Головна</a></li>
                  <li><a href="./customers_list.php">Клієнти</a></li>
                  <li><a href="./books_list.php">Книги</a></li>
                  <li><a href="./author_list.php">Автори</a></li> 
                  <li><a href="./librarians_list.php">Працівники</a></li>
                  <li><a href="./provision_list.php">Видача книг</a></li>
                  <li><a href="?logOut=1" id="logOut">Вийти</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if($success): ?>
    <div class="validation-msg done" style="margin: 15px 0;">
        <img src='../images/done.svg' alt='done' style="width: 50px;">
        <h2 class='validation-text'>Клієнта успішно додано до системи</h2>      
    </div>
    <?php endif; ?>

    <?php if($db_error): ?>
    <div class='validation-msg error' style="margin: 20px auto; max-width: 600px; background: #f2dede; padding: 15px; border-radius: 5px;">
        <h2 class='validation-text' style="color: #a94442;">Помилка бази даних. Перевірте з'єднання.</h2>      
    </div>
    <?php endif; ?>

    <div class="container main-content add" style="padding-top: 20px;">
        <div class="row" style="display:flex; align-items:stretch;">
        <div class="col-lg-6 form-container">
            <form class="book-form" method="POST">
                <h1 class="text-center">Реєстрація користувача</h1>
                <label>Ім'я</label>
                <input type="text" class="form-input" name="fname" required>
                <label>По батькові</label>
                <input type="text" class="form-input" name="pname" required>
                <label>Прізвище</label>
                <input type="text" class="form-input" name="sname" required>
                <label>Адреса</label>
                <input type="text" class="form-input" name="address" required>
                <label>Телефон</label>
                <input type="text" class="form-input" name="phone" placeholder="+380..." required>
                <label>Місце роботи/навчання</label>
                <input type="text" class="form-input" name="employment" required>
                <div class="line">
                    <input class="submit" type="submit" name="add-customer" value="Зареєструвати">
                </div>
            </form>
        </div>
        <div class="col-lg-6 image hidden-xs">
            <img src="../images/add_user.png" class="img-responsive" style="height:100%; object-fit:contain;">
        </div>
        </div>
    </div>

    <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="#"><img src="../images/icon_facebook.svg" alt="фейсбук"></a>
            <a href="#"><img src="../images/icon-instagram.svg" alt="інстаграм"></a>
            <a href="#"><img src="../images/icon-twitterx.svg" alt="ікс"></a>
        </div>
        <div class="col-lg-3">
            <p>Зв’яжіться з нами: +380-88-675-89-12</p>
        </div>
        <div class="col-lg-12 text-center">
            <p>© 2026 LibraVerse. Всі права захищені.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>
<?php
} else {
    header("Location: ../index.php");
    exit;
}
?>