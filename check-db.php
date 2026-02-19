<?php
declare(strict_types=1);

$u = getenv('JAWSDB_URL');
if (!$u) { fwrite(STDERR, "No JAWSDB_URL\n"); exit(1); }

$p = parse_url($u);
if ($p === false) { fwrite(STDERR, "Invalid JAWSDB_URL\n"); exit(1); }

$host = $p['host'] ?? '';
$user = $p['user'] ?? '';
$pass = $p['pass'] ?? '';
$db   = isset($p['path']) ? ltrim($p['path'], '/') : '';
$port = $p['port'] ?? 3306;

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
$tableCount = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();

echo "DB={$dbName}\n";
echo "Tables={$tableCount}\n";

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "{$t}={$count}\n";
}
