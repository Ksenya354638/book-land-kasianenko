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

    $row1 = $conn->query("SELECT COUNT(*) AS LibrarianNumber FROM librarians")->fetch(PDO::FETCH_ASSOC);
    $row2 = $conn->query("SELECT COUNT(*) AS LibrarianNumber FROM librarians WHERE Position='бібліотекар'")->fetch(PDO::FETCH_ASSOC);
    $row3 = $conn->query("SELECT COUNT(*) AS LibrarianNumber FROM librarians WHERE Position='завідувач'")->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Помилка підключення";
    exit;
}
?>
<div class="col-lg-4 statistics books">
    <h2>Загальна кількість працівників</h2>
    <p class="number"><?php echo $row1['LibrarianNumber']; ?></p>
</div>
<div class="col-lg-4 statistics books">
    <h2>Кількість бібліотекарів</h2>
    <p class="number"><?php echo $row2['LibrarianNumber']; ?></p>
</div>
<div class="col-lg-4 statistics books">
    <h2>Кількість завідувачів</h2>
    <p class="number"><?php echo $row3['LibrarianNumber']; ?></p>
</div>