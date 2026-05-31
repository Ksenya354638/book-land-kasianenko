<?php
session_start();
// Підключення до БД (PDO)
$host = getenv('DB_HOST'); $port = getenv('DB_PORT'); $dbname = getenv('DB_NAME');
$user = getenv('DB_USER'); $pass = getenv('DB_PASSWORD');

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Помилка БД"); }

if(isset($_SESSION['LibrarianID'])) {
    if(isset($_GET['CustomerID'])) {
        $customerID = $_GET['CustomerID'];

       // 1. Отримання даних клієнта
        $stmt = $conn->prepare("SELECT * FROM customers WHERE CustomerID = ?");
        $stmt->execute([$customerID]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Книги на руках (Шукаємо тільки ті, де ReturnDate є NULL)
        $stmtActive = $conn->prepare("SELECT bp.ProvisionID, bp.BookID, bp.LibrarianID, b.Title, l.FirstName, l.Surname, bp.ReceiptDate 
            FROM booksprovision bp 
            JOIN books b ON bp.BookID = b.BookID 
            JOIN librarians l ON bp.LibrarianID = l.LibrarianID 
            WHERE bp.CustomerID = ? AND bp.ReturnDate IS NULL"); 
        $stmtActive->execute([$customerID]);
        $activeBooks = $stmtActive->fetchAll(PDO::FETCH_ASSOC);

        // 3. Історія (Тільки ті, де ReturnDate МАЄ значення)
        $stmtHistory = $conn->prepare("SELECT bp.ProvisionID, bp.BookID, b.Title, l.FirstName, l.Surname, bp.ReceiptDate, bp.ReturnDate 
            FROM booksprovision bp 
            JOIN books b ON bp.BookID = b.BookID 
            JOIN librarians l ON bp.LibrarianID = l.LibrarianID 
            WHERE bp.CustomerID = ? AND bp.ReturnDate IS NOT NULL
            ORDER BY bp.ReturnDate DESC");
        $stmtHistory->execute([$customerID]);
        $historyBooks = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

        // --- ЛОГІКА ДІЙ ---
        if(isset($_POST['delete'])) {
            $del = $conn->prepare("DELETE FROM customers WHERE CustomerID = ?");
            $del->execute([$customerID]);
            header("Location: ./customers_list.php?msg=deleted");
            exit;
        }

        if(isset($_POST['return'])) {
            $provID = $_POST['return'];
            $bookID = $_POST['bookID'];
            $now = date("Y-m-d");
            $conn->prepare("UPDATE booksprovision SET ReturnDate = ? WHERE ProvisionID = ?")->execute([$now, $provID]);
            $conn->prepare("UPDATE books SET Status = 'в наявності' WHERE BookID = ?")->execute([$bookID]);
            header("Location: ./customer_profile.php?CustomerID=$customerID");
            exit;
        }

        if(isset($_POST['provide'])) {
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
    <title>Профіль клієнта | LibraVerse</title>
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
            <button type="submit" name="provide" class="add" style="width:100%;">
                Видати нову книгу
            </button>
        </form>

    </div>
</div>

            <hr>

            <h3>Книги на руках:</h3>
            <table class="table result-table col-lg-12" style="margin-top:15px;">
                <tr>
                    <th>ID</th><th>Книга</th><th>Бібліотекар</th><th>Дата видачі</th><th>Дія</th>
                </tr>
                <?php if (empty($activeBooks)): ?>
                    <tr><td colspan="5" class="text-center">Немає книг на руках</td></tr>
                <?php else: ?>
                    <?php foreach ($activeBooks as $row): ?>
                    <tr>
                        <td><?php echo $row['ProvisionID']; ?></td>
                        <td><a href="./book_profile.php?BookID=<?php echo $row['BookID']; ?>"><?php echo htmlspecialchars($row['Title']); ?></a></td>
                        <td><?php echo htmlspecialchars($row['FirstName'] . " " . $row['Surname']); ?></td>
                        <td><?php echo $row['ReceiptDate']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="bookID" value="<?php echo $row['BookID']; ?>">
                                    <button 
                                        type="submit" 
                                        name="return" 
                                        value="<?php echo $row['ProvisionID']; ?>" 
                                        class="provide"
                                        style="width:100%; margin-top:5px;">
                                    Повернуто
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>

            <h3 class="mt-5">Історія повернутих книг:</h3>
            <table class="table result-table col-lg-12" style="margin-top:15px;">
                <tr>
                    <th>ID</th><th>Книга</th><th>Видано</th><th>Повернено</th>
                </tr>
                <?php if (empty($historyBooks)): ?>
                    <tr><td colspan="4" class="text-center">Історія порожня</td></tr>
                <?php else: ?>
                    <?php foreach ($historyBooks as $row): ?>
                    <tr>
                        <td><?php echo $row['ProvisionID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td><?php echo $row['ReceiptDate']; ?></td>
                        <td><?php echo $row['ReturnDate']; ?></td>
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
    } 
} else { 
    header("Location: ../index.php"); 
    exit;
} 
?>