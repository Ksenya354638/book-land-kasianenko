<?php
session_start();
// Підключення до БД (PDO)
$host = getenv('DB_HOST'); $port = getenv('DB_PORT'); $dbname = getenv('DB_NAME');
$user = getenv('DB_USER'); $pass = getenv('DB_PASSWORD');

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Помилка БД"); }

if(isset($_SESSION['EmployeeID'])) {

if(isset($_GET['logOut'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
    if(isset($_GET['CustomerID'])) {
        $customerID = $_GET['CustomerID'];

       // 1. Отримання даних клієнта
        $stmt = $conn->prepare("SELECT * FROM customers WHERE CustomerID = ?");
        $stmt->execute([$customerID]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtSales = $conn->prepare("
SELECT
s.SaleID,
s.SaleDate,
s.Quantity,
b.BookID,
b.Title,
e.FirstName,
e.Surname
FROM sales s
JOIN books b
ON s.BookID=b.BookID
JOIN employees e
ON s.EmployeeID=e.EmployeeID
WHERE s.CustomerID=?
ORDER BY s.SaleDate DESC
");

$stmtSales->execute([$customerID]);

$salesHistory =
$stmtSales->fetchAll(PDO::FETCH_ASSOC);


        // --- ЛОГІКА ДІЙ ---
        if(isset($_POST['delete'])) {
            $del = $conn->prepare("DELETE FROM customers WHERE CustomerID = ?");
            $del->execute([$customerID]);
            header("Location: ./customers_list.php?msg=deleted");
            exit;
        }


        if(isset($_POST['sell'])) {
            $_SESSION['CustomerID'] = $customerID;
            header("Location: ./books_list.php"); 
            exit;
        }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Профіль клієнта | BookLand</title>
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
                  <li><a href="./sales_list.php">Продаж книг</a></li>
                  <li><a href="?logOut=1" id="logOut">Вийти</a></li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container main-content customer-profile">
        <?php if ($customer): ?>
            <div class="row profile-header" style="margin-top: 20px;">
    <div class="col-md-8 book-descript">
        <h1><?php echo htmlspecialchars($customer['Surname'] . " " . $customer['FirstName']); ?></h1>
        <p><b>Адреса:</b> <?php echo htmlspecialchars($customer['Address']); ?></p>
        <p><b>Телефон:</b> <?php echo htmlspecialchars($customer['PhoneNumber']); ?></p>
        <p><b>Робота:</b> <?php echo htmlspecialchars($customer['Employment']); ?></p>
    </div>

    <div class="col-md-4 buttons right" style="margin-top: 10px;">
        
        <form method="POST" onsubmit="return confirm('Видалити клієнта?')" style="margin-bottom:10px;">
            <button type="submit" name="delete" class="delete" style="width:100%;">
                Видалити профіль
            </button>
        </form>

        <form method="POST">
            <button type="submit" name="sell" class="add" style="width:100%;">
                Оформити покупку
            </button>
        </form>

    </div>
</div>

            <hr>

            <h3>Історія покупок:</h3>
<table class="table result-table col-lg-12" style="margin-top:15px;">
    <tr>
        <th>ID продажу</th>
        <th>Книга</th>
        <th>Продавець</th>
        <th>Дата продажу</th>
        <th>Кількість</th>
    </tr>

    <?php if(empty($salesHistory)): ?>
        <tr>
            <td colspan="5" class="text-center">
                Покупок не знайдено
            </td>
        </tr>
    <?php else: ?>
        <?php foreach($salesHistory as $row): ?>
            <tr>
                <td><?php echo $row['SaleID']; ?></td>
                <td>
                    <a href="./book_profile.php?BookID=<?php echo $row['BookID']; ?>">
                        <?php echo htmlspecialchars($row['Title']); ?>
                    </a>
                </td>
                <td>
                    <?php echo htmlspecialchars($row['FirstName'].' '.$row['Surname']); ?>
                </td>
                <td><?php echo $row['SaleDate']; ?></td>
                <td><?php echo $row['Quantity']; ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

        <?php else: ?>
            <div class="alert alert-danger">Клієнта не знайдено!</div>
        <?php endif; ?>
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
    } 
} else { 
    header("Location: ../index.php"); 
    exit;
} 
?>