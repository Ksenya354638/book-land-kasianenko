<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: 11878;
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 1. Загальна кількість працівників
    $totalStmt = $conn->query("SELECT COUNT(*) AS total FROM employees");
    $totalEmployees = $totalStmt->fetch()['total'] ?? 0;

    // 2. Кількість за посадами (одним запитом)
    $stmt = $conn->query("
        SELECT Position, COUNT(*) AS cnt
        FROM employees
        GROUP BY Position
    ");

    $positions = [];
    while ($row = $stmt->fetch()) {
        $positions[$row['Position']] = $row['cnt'];
    }

    // Безпечні значення (на випадок відсутності позицій)
    $seller1 = $positions['продавець на повний робочий день'] ?? 0;
    $seller2   = $positions['продавець на неповний робочий день'] ?? 0;

} catch (PDOException $e) {
    $totalEmployees = 0;
    $seller1 = 0;
    $seller2 = 0;
}
?>

<div class="col-lg-4 statistics books">
    <h2>Загальна кількість працівників</h2>
    <p class="number"><?= htmlspecialchars($totalEmployees) ?></p>
</div>

<div class="col-lg-4 statistics books">
    <h2>Кількість продавців на повний робочий день</h2>
    <p class="number"><?= htmlspecialchars($seller1) ?></p>
</div>

<div class="col-lg-4 statistics books">
    <h2>Кількість продавців на неповний робочий день</h2>
    <p class="number"><?= htmlspecialchars($seller2) ?></p>
</div>