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
    die("Помилка БД");
}

if(isset($_SESSION['EmployeeID'])){
    if(isset($_GET['AuthorID'])) {
        $authorID = $_GET['AuthorID'];

        // Отримання даних автора
        $stmt1 = $conn->prepare("SELECT * FROM authors WHERE AuthorID = ?");
        $stmt1->execute([$authorID]);
        $author = $stmt1->fetch(PDO::FETCH_ASSOC);

        // Отримання книг автора
        $stmt2 = $conn->prepare("SELECT b.BookID,
            b.BookCover, b.Title, b.Quantity, b.Availability,
            a.AuthorID, a.Name, a.Surname
            FROM books b JOIN authors a ON b.AuthorID=a.AuthorID
            WHERE a.AuthorID=?
            ");
        $stmt2->execute([$authorID]);

        // Видалення автора
        if(isset($_POST['delete'])) {
            $conn->prepare("DELETE FROM books WHERE AuthorID = ?")->execute([$authorID]);
            $conn->prepare("DELETE FROM authors WHERE AuthorID = ?")->execute([$authorID]);
            header("Location: ./author_list.php?msg=deleted");
            exit;
        }

        // Логіка видачі книги
        if(isset($_POST['sell'])) {

    if(isset($_SESSION['CustomerID'])) {

        $bookID = $_POST['BookID'];

        $customerID = $_SESSION['CustomerID'];

        $employeeID = $_SESSION['EmployeeID'];

        $saleDate = date("Y-m-d");

        $bookStmt = $conn->prepare("
        SELECT Quantity
        FROM books
        WHERE BookID=?
        ");

        $bookStmt->execute([$bookID]);

        $bookData = $bookStmt->fetch(PDO::FETCH_ASSOC);

        if($bookData && $bookData['Quantity'] > 0) {

            $conn->prepare("
            INSERT INTO sales
            (BookID, CustomerID, EmployeeID, SaleDate, Quantity)
            VALUES (?, ?, ?, ?, 1)
            ")
            ->execute([
                $bookID,
                $customerID,
                $employeeID,
                $saleDate
            ]);

            $newQuantity = $bookData['Quantity'] - 1;

            $availability =
                ($newQuantity > 0)
                ? 'В наявності'
                : 'Немає в наявності';

            $conn->prepare("
            UPDATE books
            SET Quantity=?,
                Availability=?
            WHERE BookID=?
            ")
            ->execute([
                $newQuantity,
                $availability,
                $bookID
            ]);

            unset($_SESSION['CustomerID']);

            header("Location: ./sales_list.php");
            exit;
        }
    }
}
    }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Профіль автора</title>
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

    <?php if ($author) { ?>
    <div class="container main-content profile">
        <div class="row">
    <div class="col-lg-8 book-descript">
        <h1><?php echo $author['Name'];?> <?php echo $author['Surname'];?></h1>
        <p><b>Роки життя:</b> <?php echo $author['BirthYear'];?> - <?php echo $author['DeathYear'];?></p>
        <div class="biography"><?php echo $author['Biography']; ?></div>
    </div>

    <div class="col-lg-4 buttons right">
        <form method="POST" onsubmit="return confirm('Ви впевнені?');" style="width:100%;">
            <button type="submit" name="delete" class="delete" style="width:100%;">
                Видалити автора та його книги
            </button>
        </form>

        <a href="./new_book.php?AuthorID=<?php echo $authorID; ?>" class="add" style="width:100%;">
            Додати книгу
        </a>
    </div>
</div>

            <hr>
            <h2>Книги автора:</h2>
            <div class="row">
                <?php while ($book = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                <div class="col-lg-3 about-book">
    <div class="book-description">
        <img src="<?php echo $book['BookCover']; ?>" class="img-responsive">
        <h3>
            <a href="./book_profile.php?BookID=<?php echo $book['BookID']; ?>">
                <?php echo htmlspecialchars($book['Title']); ?>
            </a>
        </h3>
        <p class="status"><?php echo $book['Availability']; ?></p>
        <?php if ($book['Quantity'] > 0) { ?>
            <form method="POST">
                <input type="hidden" name="BookID" value="<?php echo $book['BookID']; ?>">
                <button type="submit" name="provide" class="provide">
                    Продати
                </button>
            </form>
        <?php } ?>
    </div>
</div>
                <?php } ?>
            </div>
    </div>
    <?php } else { ?>
        <div class="validation-msg">
            <h2>Автора не знайдено! <a href="./author_list.php">Назад</a></h2>
        </div>
    <?php } ?>

    <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="https://www.facebook.com/?locale=uk_UA">
                <img src="./images/icon_facebook.svg" alt="фейсбук">
            </a>
            <a href="https://www.instagram.com/">
                <img src="./images/icon-instagram.svg" alt="інстаграм">
            </a>
            <a href="https://twitter.com/?lang=uk">
                <img src="./images/icon-twitterx.svg" alt="ікс">
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
    header("Location: ../index.php");
    exit;
} 
?>