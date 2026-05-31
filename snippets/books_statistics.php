<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $row1 = $conn->query("SELECT COUNT(*) AS BooksNumber FROM books")->fetch(PDO::FETCH_ASSOC);
    $row2 = $conn->query("SELECT COUNT(*) AS BooksNumber FROM books WHERE Status='в наявності'")->fetch(PDO::FETCH_ASSOC);
    $row3 = $conn->query("SELECT COUNT(*) AS BooksNumber FROM books WHERE Status='на руках'")->fetch(PDO::FETCH_ASSOC);
    $row4 = $conn->query("SELECT AVG(Price) AS BooksPrice FROM books")->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='validation-msg'><h2 class='validation-text'>Помилка бази даних</h2></div>";
    exit;
}
?>
<div class="col-lg-3 statistics books">
    <h2>Загальна кількість книг в системі</h2>
    <p class="number"><?php echo $row1['BooksNumber']; ?></p>
</div>
<div class="col-lg-3 statistics books">
    <h2>Кількість книг в наявності</h2>
    <p class="number"><?php echo $row2['BooksNumber']; ?></p>
</div>
<div class="col-lg-3 statistics books">
    <h2>Кількість книг на руках</h2>
    <p class="number"><?php echo $row3['BooksNumber']; ?></p>
</div>
<div class="col-lg-3 statistics books">
    <h2>Середня вартість книги</h2>
    <p class="number"><?php echo round($row4['BooksPrice']); ?> грн</p>
</div>