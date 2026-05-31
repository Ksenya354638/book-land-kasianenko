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
    die("Помилка підключення до бази даних");
}

if (isset($_SESSION['LibrarianID'])) {
    $success_msg = false;
    
    if (isset($_POST['add-author'])) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $birthYear = $_POST['birthYear'] ?: null;
        $deathYear = $_POST['deathYear'] ?: 0;
        $biography = $_POST['biography'];

        $query = $conn->prepare("INSERT INTO authors (Name, Surname, BirthYear, DeathYear, Biography) VALUES (?, ?, ?, ?, ?)");
        if ($query->execute([$name, $surname, $birthYear, $deathYear, $biography])) {
            $success_msg = true;
        }
    }

    if (isset($_GET['logOut'])) {
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
    <title>Додати автора | LibraVerse</title>
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

    <?php if ($success_msg): ?>
        <div class='validation-msg done'>
            <img src='../images/done.svg' alt='done'>
            <h2 class='validation-text'>Автора успішно додано до каталогу</h2>     
        </div>
    <?php endif; ?>

    <div class="container main-content add">
        <div class="row">
            <div class="col-lg-6 form-container">
                <form class="book-form" method="POST">
                    <h1 class="text-center">Додати нового автора</h1>
                    <label for="name">Ім'я</label>
                    <input type="text" class="form-input" id="name" name="name" required>
                    <label for="surname">Прізвище</label>
                    <input type="text" class="form-input" id="surname" name="surname" required>
                    <label for="birthYear">Рік народження</label>
                    <input type="number" class="form-input" id="birthYear" name="birthYear">
                    <label for="deathYear">Рік смерті (0 якщо живий)</label>
                    <input type="number" class="form-input" id="deathYear" name="deathYear" value="0">
                    <label for="biography">Біографія</label>
                    <textarea rows="4" class="form-input abstract" id="biography" name="biography" required></textarea>
                    <div class="line">
                        <input class="submit btn-success" type="submit" name="add-author" value="Додати автора">
                    </div>
                </form>
            </div>
            <div class="col-lg-6 image hidden-xs">
                <img src="../images/add_author.png" class="img-responsive">
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