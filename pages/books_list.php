<?php
session_start();

// Налаштування підключення до БД (PDO)
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
    die("<div class='validation-msg'><h2>Помилка підключення!</h2></div>");
}

// Перевірка авторизації
if (isset($_SESSION['EmployeeID'])) {
    
    // Логіка фільтрації категорій
    $category_map = [
        'fiction'   => 'художня',
        'medical'   => 'медична',
        'technical' => 'технічна',
        'economic'  => 'економічна',
        'computer'  => 'комп\'ютерна',
        'natural'   => 'природознавча',
        'legal'     => 'юридична',
        'general'   => 'загальна'
    ];

    $query = "SELECT b.BookID, b.BookCover, b.Title, b.Quantity,
                b.Availability, a.AuthorID, a.Name, a.Surname 
                FROM books b 
                JOIN authors a ON b.AuthorID = a.AuthorID";
    
    $params = [];
    if (isset($_POST['sort']) && array_key_exists($_POST['sort'], $category_map)) {
        $query .= " WHERE b.Category = :category";
        $params[':category'] = $category_map[$_POST['sort']];
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

if (isset($_POST['sell'])) {

    if (isset($_SESSION['CustomerID']) && isset($_POST['BookID'])) {

        $customerID = $_SESSION['CustomerID'];
        $employeeID = $_SESSION['EmployeeID'];
        $bookID = $_POST['BookID'];
        $saleDate = date('Y-m-d');

        // беремо книгу з БД
        $stmtBook = $conn->prepare("SELECT Quantity FROM books WHERE BookID = ?");
        $stmtBook->execute([$bookID]);
        $book = $stmtBook->fetch(PDO::FETCH_ASSOC);

        if ($book && $book['Quantity'] > 0) {

            $conn->prepare("
                INSERT INTO sales (BookID, CustomerID, EmployeeID, SaleDate, Quantity)
                VALUES (?, ?, ?, ?, 1)
            ")->execute([
                $bookID,
                $customerID,
                $employeeID,
                $saleDate
            ]);

            $newQuantity = $book['Quantity'] - 1;

            $availability = ($newQuantity > 0)
                ? 'в наявності'
                : 'немає в наявності';

            $conn->prepare("
                UPDATE books
                SET Quantity = ?, Availability = ?
                WHERE BookID = ?
            ")->execute([
                $newQuantity,
                $availability,
                $bookID
            ]);

            unset($_SESSION['CustomerID']);

            header("Location: ./sales_list.php");
            exit;
        }
    } else {
        header("Location: ./customers_list.php?action=select_customer");
        exit;
    }
}

    // Логаут
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
    <title>BookLand - Каталог книг</title>
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


    <div class="main-content container">
    
    <div class="table-header">
        <h1 class="text-center">Книжковий каталог</h1>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <div class="side-menu">
                <form method="POST">
                    <h3 class="text-center">Оберіть категорію</h3>
                    <button type="submit" name="sort" value="all">Всі книги</button><br>
                    <button type="submit" name="sort" value="fiction">Художня література</button><br>
                    <button type="submit" name="sort" value="medical">Медична література</button><br>
                    <button type="submit" name="sort" value="technical">Технічна література</button><br>
                    <button type="submit" name="sort" value="economic">Економічна література</button><br>
                    <button type="submit" name="sort" value="computer">Комп'ютерна література</button><br>
                    <button type="submit" name="sort" value="natural">Природознавча література</button><br>
                    <button type="submit" name="sort" value="legal">Юридична література</button><br>
                </form>
            </div>
            <div class="button">
                <a href="./new_book.php" class="add">додати книгу</a>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="row">
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                
                <div class="about-book col-md-4 col-sm-6">
                    
                    <div class="book-cover">
                        <img src="<?php echo htmlspecialchars($row['BookCover']); ?>" alt="обкладинка">
                    </div>

                    <div class="book-description">
                        
                        <h3>
                            <a href="./book_profile.php?BookID=<?php echo $row['BookID']; ?>">
                                <?php echo htmlspecialchars($row['Title']); ?>
                            </a>
                        </h3>

                        <p class="author">
                            <a href="./author_profile.php?AuthorID=<?php echo $row['AuthorID']; ?>">
                                <?php echo htmlspecialchars($row['Name'] . ' ' . $row['Surname']); ?>
                            </a>
                        </p>

                        <div class="book-actions">
                            <?php if ($row['Quantity'] > 0) { ?>
                            <form method="POST" class="sell-form">
                                <input type="hidden" name="BookID" value="<?php echo $row['BookID']; ?>">
                                <button class="sell-btn" type="submit" name="sell">Продати</button>
                            </form>
                                <?php } ?>
                            <div class="status-badge <?php echo ($row['Availability'] === 'в наявності') ? 'available' : 'taken'; ?>">
                                <?php echo htmlspecialchars($row['Availability']); ?>
                            </div>
                        </div>

                    </div>                    
                </div>

            <?php } ?>
            </div>
        </div>
    </div>
</div>

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


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>
<?php 
} else { 
    // Блок для неавторизованих користувачів
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Помилка доступу</title>
</head>
<body>
    <div class="container text-center" style="margin-top:100px;">
        <img src="../images/error.svg" width="100">
        <h1>Ви не авторизовані!</h1>
        <p>Поверніться до <a href="../index.php">сторінки входу</a></p>
    </div>
</body>
</html>
<?php } ?>