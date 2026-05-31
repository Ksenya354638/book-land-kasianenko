<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 7,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. Загальна кількість клієнтів
    $row1 = $conn->query("SELECT COUNT(*) AS CustomerNumber FROM customers")->fetch(PDO::FETCH_ASSOC);

    // 2. Кількість унікальних клієнтів-боржників
    $row2 = $conn->query("SELECT COUNT(DISTINCT CustomerID) AS CustomerNumber 
                          FROM booksprovision 
                          WHERE ReturnDate IS NULL")->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Помилка завантаження статистики</div>";
    exit;
}
?>

<div class="col-lg-6 statistics customers">
    <h2>Загальна кількість клієнтів в системі</h2>
    <p class="number"><?php echo htmlspecialchars($row1['CustomerNumber'] ?? 0); ?></p>
</div>

<div class="col-lg-6 statistics customers">
    <h2>Кількість клієнтів, які мають видані книги</h2>
    <p class="number"><?php echo htmlspecialchars($row2['CustomerNumber'] ?? 0); ?></p>
</div>