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
    die("<div class='validation-msg'><h2>Помилка підключення до бази даних</h2></div>");
}

if (isset($_SESSION['LibrarianID'])) {
    $book = null;
    
    if (isset($_GET['BookID'])) {
        $bookID = $_GET['BookID'];

        $stmt = $conn->prepare("SELECT b.*, a.Name, a.Surname, a.AuthorID 
                                 FROM books b 
                                 JOIN authors a ON b.AuthorID = a.AuthorID 
                                 WHERE b.BookID = ?");
        $stmt->execute([$bookID]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (isset($_POST['delete'])) {
            $deleteStmt = $conn->prepare("DELETE FROM books WHERE BookID = ?");
            $deleteStmt->execute([$bookID]);
            header("Location: ./books_list.php?msg=deleted");
            exit;
        }

        if (isset($_POST['provide'])) {
            if (isset($_SESSION['CustomerID'])) {
                $customerID = $_SESSION['CustomerID'];
                $librarianID = $_SESSION['LibrarianID'];
                $receiptDate = date("Y-m-d");

                $provideStmt = $conn->prepare("INSERT INTO booksprovision (BookID, CustomerID, LibrarianID, ReceiptDate, ReturnDate) 
                                               VALUES (?, ?, ?, ?, NULL)");
                $provideStmt->execute([$bookID, $customerID, $librarianID, $receiptDate]);

                $updateStmt = $conn->prepare("UPDATE books SET Status = 'на руках' WHERE BookID = ?");
                $updateStmt->execute([$bookID]);

                unset($_SESSION['CustomerID']);
                header("Location: ./provision_list.php");
                exit;
            } else {
                header("Location: ./customers_list.php?action=select_for_book&BookID=" . $bookID);
                exit;
            }
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
    <title>LibraVerse - Профіль книги</title>
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

    <div class="container main-content profile">
        <?php if ($book): ?>
            <div class="row">
                <div class="col-md-4">
    <div class="book-cover-large">
        <?php 
            $coverPath = !empty($book['BookCover']) ? $book['BookCover'] : "../images/books_img/no_cover.png";
        ?>
        <img class="img-responsive" src="<?php echo htmlspecialchars($coverPath); ?>" 
             alt="Обкладинка: <?php echo htmlspecialchars($book['Title']); ?>" 
             class="img-responsive img-thumbnail"
             style="width: 100%; max-height: 500px; object-fit: contain;">
        </div>
    </div>

                <div class="col-md-5 book-descript">
                    <h1><?php echo htmlspecialchars($book['Title']); ?></h1>
                    <p><b>Автор:</b> <a href="./author_profile.php?AuthorID=<?php echo $book['AuthorID']; ?>">
                        <?php echo htmlspecialchars($book['Name'] . " " . $book['Surname']); ?></a></p>
                    
                    <p><b>Жанр (Категорія):</b> <?php echo htmlspecialchars($book['Category'] ?? 'Не вказано'); ?></p>
                    <p><b>Рік:</b> <?php echo htmlspecialchars($book['Year']); ?></p>
                    <p><b>Ціна:</b> <?php echo htmlspecialchars($book['Price']); ?> грн.</p>
                    <p><b>Статус:</b> 
                        <div class="status-badge <?php echo ($book['Status'] == 'в наявності') ? 'available' : 'taken'; ?>">
                            <?php echo htmlspecialchars($book['Status']); ?>
                        </div>
                    </p>
                    
                    <?php if (!empty($book['Abstract'])): ?>
                        <div class="abstract">
                            <h3>Анотація</h3>
                            <p><?php echo nl2br(htmlspecialchars($book['Abstract'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3 text-right">
                    <form method="POST">
                        <div class="buttons right">
                            <?php if ($book['Status'] == 'в наявності'): ?>
                            <button type="submit" name="provide" class="provide">
                                Видати книгу
                            </button>
                            <?php else: ?>
                            <div class="status">Книга на руках</div>
                            <?php endif; ?>

                            <button type="submit" name="delete" class="delete"
                                onclick="return confirm('Ви впевнені?')">
                                Видалити з бази
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Книгу з таким ID не знайдено.</div>
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