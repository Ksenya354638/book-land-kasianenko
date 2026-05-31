<?php
session_start();

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: '3306';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Помилка БД: " . $e->getMessage()); 
}

if(isset($_SESSION['LibrarianID'])) {
    $success = false;
    $error_msg = "";

    if(isset($_POST['add-librarian'])){
        try {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $query = $conn->prepare("INSERT INTO librarians (FirstName, ParentalName, Surname, Address, PhoneNumber, BirthDate, EmploymentDate, Position, Password) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $query->execute([
                $_POST['fname'], 
                $_POST['pname'], 
                $_POST['sname'], 
                $_POST['address'], 
                $_POST['phone'], 
                $_POST['birthDate'], 
                $_POST['employmentDate'], 
                $_POST['position'], 
                $hashed_password
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error_msg = "Не вдалося додати працівника: " . $e->getMessage();
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
    <title>Новий працівник | LibraVerse</title>
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

    <div class="container main-content add" style="padding-top:20px;">

    <?php if($success): ?>
        <div class="alert alert-success text-center">
            <h4>Успішно! Працівника додано до системи.</h4>
        </div>
    <?php endif; ?>

    <?php if($error_msg): ?>
        <div class="alert alert-danger text-center">
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div class="row librarian-layout">
    
    <!-- ФОРМА -->
    <div class="col-lg-7 col-md-7 col-sm-12 form-container d-flex">
        <div class="book-form librarian-form">

            <h1 class="text-center form-title">
                Реєстрація працівника
            </h1>

            <form method="POST" class="form-vertical">

                <div class="form-group">
                    <label>Ім'я</label>
                    <input type="text" class="form-input" name="fname" required>
                </div>

                <div class="form-group">
                    <label>По батькові</label>
                    <input type="text" class="form-input" name="pname" required>
                </div>

                <div class="form-group">
                    <label>Прізвище</label>
                    <input type="text" class="form-input" name="sname" required>
                </div>

                <div class="form-group">
                    <label>Адреса</label>
                    <input type="text" class="form-input" name="address" required>
                </div>

                <div class="form-group">
                    <label>Телефон</label>
                    <input type="text" class="form-input" name="phone" required>
                </div>

                <div class="form-group">
                    <label>Дата народження</label>
                    <input type="date" class="form-input" name="birthDate" required>
                </div>

                <div class="form-group">
                    <label>Дата прийому</label>
                    <input type="date" class="form-input" name="employmentDate" required>
                </div>

                <div class="form-group">
                    <label>Посада</label>
                    <select name="position" class="form-input">
                        <option value="бібліотекар">Бібліотекар</option>
                        <option value="завідуючий">Завідуючий</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" class="form-input" name="password" required>
                </div>

                <button type="submit"
                        name="add-librarian"
                        class="btn btn-primary btn-block submit-btn">
                    Зареєструвати
                </button>

            </form>
        </div>
    </div>

    <!-- ЗОБРАЖЕННЯ -->
    <div class="col-lg-5 col-md-5 hidden-xs image-container">
        <img src="../images/add_librarian.png" class="img-responsive librarian-img">
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
</body>
</html>
<?php
} else { header("Location: ../index.php"); exit; }
?>