<?php
session_start();

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$port = getenv('DB_PORT') ?: '3306'; 

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 7,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Помилка підключення до бази даних.");
}

// Перевірка авторизації
if(isset($_SESSION['EmployeeID'])) {
    
    // 1. Статистика
    $stats = $conn->query("
        SELECT
            COUNT(*) AS total_sales,
            SUM(Quantity) AS total_books
            FROM sales
    ")->fetch(PDO::FETCH_ASSOC);
    $total_sales = $stats['total_sales'] ?? 0;
    $total_books = $stats['total_books'] ?? 0;

    $count_all = $stats['total_sales'] ?? 0;

    // 2. Основний запит
    $query = "SELECT s.SaleID, s.BookID, s.CustomerID, b.Title, a.Name AS aName, a.Surname AS aSurname,
                    c.FirstName, c.ParentalName, c.Surname AS cSurname, c.PhoneNumber, s.SaleDate,
                    s.Quantity
                FROM sales s JOIN books b ON b.BookID = s.BookID JOIN authors a ON a.AuthorID = b.AuthorID
                JOIN customers c ON c.CustomerID = s.CustomerID
                ORDER BY s.SaleDate DESC";

}
    
    $sales = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Продажі книг | BookLand</title>
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

    <div class="container main-content">
        <div class="table-header">
            <h1 class="text-center">Журнал продажів</h1>
        </div>

        <div class="row info-stats">
        <div class="col-md-6">
            <div class="stat-card danger">
                <h4>Всього продажів</h4>
                <span><?php echo $total_sales; ?></span>
            </div>
        </div>
        <div class="col-md-6">
                <div class="stat-card success">
                    <h4>Продано примірників</h4>
                    <span><?php echo $total_books; ?></span>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="result-table table table-hover">
                        <thead>
                            <tr>
                                <th>Книга</th>
                                <th>Автор</th>
                                <th>Покупець</th>
                                <th>Дата продажу</th>
                                <th>Кількість</th>
                                <th style="min-width: 150px;">Кількість</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($sales as $row): ?>
    <tr>
        <td>
            <a href="book_profile.php?BookID=<?php echo $row['BookID']; ?>">
                <?php echo htmlspecialchars($row['Title']); ?>
            </a>
        </td>

        <td>
            <?php echo htmlspecialchars($row['aName'] . ' ' . $row['aSurname']); ?>
        </td>

        <td>
            <a href="customer_profile.php?CustomerID=<?php echo $row['CustomerID']; ?>">
                <?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['cSurname']); ?>
            </a>
        </td>

        <td>
            <?php echo htmlspecialchars($row['SaleDate']); ?>
        </td>

        <td>
            <?php echo htmlspecialchars($row['Quantity']); ?>
        </td>
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
            <p>© 2026 BookLand. Kasianenko A.V.Всі права захищені.</p>
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