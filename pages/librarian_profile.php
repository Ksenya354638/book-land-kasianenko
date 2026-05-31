<?php
session_start();

// Отримання параметрів з оточення
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: '3306'; // Стандартний порт для MySQL

try {
    // Додаємо порт до DSN та встановлюємо таймаут підключення
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 5, // Таймаут 5 секунд
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Помилка підключення до бази даних. Перевірте налаштування Render.");
}

if(isset($_SESSION['LibrarianID'])) {
    $librarianID = $_GET['LibrarianID'] ?? null;
    $librarian = null;
    $provisions = [];

    if($librarianID) {
        // 1. Дані працівника
        $stmt1 = $conn->prepare("SELECT * FROM librarians WHERE LibrarianID = ?");
        $stmt1->execute([$librarianID]);
        $librarian = $stmt1->fetch(PDO::FETCH_ASSOC);

        if($librarian) {
            // 2. Список виданих книг (латинська C у CustomerID)
            $stmt2 = $conn->prepare("SELECT bp.ProvisionID, b.BookID, b.Title, 
                                            c.CustomerID, c.FirstName, c.Surname, 
                                            bp.ReceiptDate, bp.ReturnDate 
                                     FROM booksprovision bp 
                                     JOIN books b ON bp.BookID = b.BookID 
                                     JOIN customers c ON bp.CustomerID = c.CustomerID 
                                     WHERE bp.LibrarianID = ?
                                     ORDER BY bp.ReceiptDate DESC");
            $stmt2->execute([$librarianID]);
            $provisions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        // 3. Видалення
        if(isset($_POST['delete'])) {
            $stmt_del = $conn->prepare("DELETE FROM librarians WHERE LibrarianID = ?");
            $stmt_del->execute([$librarianID]);
            header("Location: librarians_list.php?deleted=success");
            exit;
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
    <title>Профіль працівника | LibraVerse</title>
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

    <div class="container main-content customer-profile">
        <?php if ($librarian): ?>
            <div class="row profile-info book-descript">
                <div class="col-md-8">
                    <h1><?php echo htmlspecialchars("{$librarian['Surname']} {$librarian['FirstName']} {$librarian['ParentalName']}"); ?></h1>
                    <p><b>ID:</b> <?php echo $librarian['LibrarianID']; ?></p>
                    <p><b>Посада:</b> <?php echo htmlspecialchars($librarian['Position']); ?></p>
                    <p><b>Телефон:</b> <?php echo htmlspecialchars($librarian['PhoneNumber']); ?></p>
                    <p><b>Адреса:</b> <?php echo htmlspecialchars($librarian['Address']); ?></p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="buttons right" style="margin-top:20px;">
                        <form method="POST" onsubmit="return confirm('Видалити цього працівника?');">
                            <button type="submit" name="delete" class="delete" style="width:100%;">
                                Видалити профіль
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 40px;">
                <div class="col-md-12">
                    <h3>Історія видач:</h3>
                    <div class="table-responsive">
                        <table class="table result-table col-lg-12">
                            <thead>
                                <tr>
                                    <th>Книга</th>
                                    <th>Клієнт</th>
                                    <th>Дата видачі</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($provisions as $row): ?>
                                <tr>
                                    <td><a href="book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo htmlspecialchars($row['Title']); ?></a></td>
                                    <td><a href="customer_profile.php?CustomerID=<?php echo $row['CustomerID']; ?>"><?php echo htmlspecialchars($row['Surname']); ?></a></td>
                                    <td><?php echo $row['ReceiptDate']; ?></td>
                                    <td><?php echo $row['ReturnDate'] ?: '<span class="status-badge taken">На руках</span>'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center">Працівника не знайдено.</div>
        <?php endif; ?>
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