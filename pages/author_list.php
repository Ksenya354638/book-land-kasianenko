<?php
session_start(); // Сесія має бути на самому початку

// Налаштування підключення до MySQL (Aiven)
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
    die("<div class='validation-msg'><h2>Помилка підключення до БД</h2></div>");
}

if(isset($_SESSION['LibrarianID'])){
    // Використовуємо LEFT JOIN, щоб бачити авторів навіть без книг
    $query = "SELECT authors.AuthorID, authors.Name, authors.Surname, authors.BirthYear, COUNT(books.BookID) AS BookCount 
              FROM authors 
              LEFT JOIN books ON books.AuthorID = authors.AuthorID 
              GROUP BY authors.AuthorID";

    if(isset($_POST['sort'])) {
        $sort_by = $_POST['sort'];
        if($sort_by === 'surname') {
            $query .= " ORDER BY authors.Surname";
        } elseif ($sort_by === 'year') {
            $query .= " ORDER BY authors.BirthYear";
        } elseif ($sort_by === 'book-count') {
            $query .= " ORDER BY BookCount DESC";
        }
    }

    $authors_result = $conn->query($query);

    if (isset($_GET['logOut'])){
        session_unset();
        header("Location: ./librarian_authorization.php");
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
    <title>LibraVerse - Автори</title>
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
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
                  <li><a href="?logOut=1">Вийти</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content container">
        <div class="table-header"><h1 class="text-center">Каталог авторів</h1></div>
        <div class="row">
            <div class="col-lg-3">
                <div class="side-menu">
                    <form method="POST">
                        <h3 class="text-center">Сортувати за:</h3>
                        <button type="submit" name="sort" value="surname">прізвищем</button><br>
                        <button type="submit" name="sort" value="year">роком народження</button><br>
                        <button type="submit" name="sort" value="book-count">кількістю творів</button>
                    </form>
                </div>
                <div class="button">
                    <a href="./new_author.php" class="add">додати автора</a>
                </div>                
            </div>
            <div class="col-lg-9">
                <div class="table-responsive">
                    <table class="result-table col-lg-12">
                        <tr>
                            <th>ID</th><th>Ім’я</th><th>Прізвище</th><th>Рік народження</th><th>Книг</th>
                        </tr>
                        <?php while ($row = $authors_result->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><a href="./author_profile.php?AuthorID=<?php echo $row['AuthorID']; ?>"><?php echo $row['AuthorID']; ?></a></td>
                            <td><?php echo $row['Name']; ?></td>
                            <td><?php echo $row['Surname']; ?></td>
                            <td><?php echo $row['BirthYear']; ?></td>
                            <td><?php echo $row['BookCount']; ?></td>
                        </tr>
                        <?php } ?>
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
    </body>
</html>
<?php 
} else { 
    // Блок для неавторизованих користувачів
    header("Location: ../index.php"); // Або туди, де у вас вхід
    exit;
} 
?>