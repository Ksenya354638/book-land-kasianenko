<?php
session_start();

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);

} catch (PDOException $e) {
    die("Помилка підключення до бази даних: " . $e->getMessage());
}

if(isset($_SESSION['LibrarianID'])) {
    // Отримуємо список усіх працівників
    $stmt = $conn->query("SELECT * FROM librarians");
    $librarians = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Логіка виходу
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
    <title>Працівники | LibraVerse</title>
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

    <div class="container main-content">
        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert alert-success text-center">Працівника успішно видалено.</div>
        <?php endif; ?>

        <div class="table-header">
            <h1 class="text-center">Список працівників</h1>
        </div>
        
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-12">
                <a href="new_librarian.php" class="btn btn-primary">Зареєструвати нового працівника</a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="result-table table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Прізвище</th>
                                <th>Ім'я</th>
                                <th>По батькові</th>
                                <th>Адреса</th>
                                <th>Номер телефону</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($librarians as $row): ?>
                                <tr>
                                    <td><a href="librarian_profile.php?LibrarianID=<?php echo $row['LibrarianID']; ?>"><?php echo $row['LibrarianID']; ?></a></td>
                                    <td><a href="librarian_profile.php?LibrarianID=<?php echo $row['LibrarianID']; ?>"><?php echo htmlspecialchars($row['Surname']); ?></a></td>
                                    <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ParentalName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
    // Відображення помилки для неавторизованих користувачів
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Помилка | LibraVerse</title>
</head>
<body>
    <div class="container text-center" style="margin-top: 100px;">
        <img src="../images/error.svg" alt="error" width="100">
        <h1>Помилка! Ви не авторизовані</h1>
        <p>Поверніться до <a href="../index.php">сторінки авторизації</a></p>
    </div>
</body>
</html>
<?php
}
?>