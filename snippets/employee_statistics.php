<?php
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
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
    $totalStmt = $conn->query("SELECT COUNT(*) AS total FROM librarians");
    $totalEmployees = $totalStmt->fetch()['total'] ?? 0;

    // 2. Кількість за посадами (одним запитом)
    $stmt = $conn->query("
        SELECT Position, COUNT(*) AS cnt
        FROM librarians
        GROUP BY Position
    ");

    $positions = [];
    while ($row = $stmt->fetch()) {
        $positions[$row['Position']] = $row['cnt'];
    }

    // Безпечні значення (на випадок відсутності позицій)
    $librarians = $positions['бібліотекар'] ?? 0;
    $managers   = $positions['завідувач'] ?? 0;

} catch (PDOException $e) {
    $totalEmployees = 0;
    $librarians = 0;
    $managers = 0;
}
?>

<div class="col-lg-4 statistics books">
    <h2>Загальна кількість працівників</h2>
    <p class="number"><?= htmlspecialchars($totalEmployees) ?></p>
</div>

<div class="col-lg-4 statistics books">
    <h2>Кількість бібліотекарів</h2>
    <p class="number"><?= htmlspecialchars($librarians) ?></p>
</div>

<div class="col-lg-4 statistics books">
    <h2>Кількість завідувачів</h2>
    <p class="number"><?= htmlspecialchars($managers) ?></p>
</div>