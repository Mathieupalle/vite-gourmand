<?php
$u = getenv('JAWSDB_URL');
if (!$u) { fwrite(STDERR, "No JAWSDB_URL\n"); exit(1); }

$p = parse_url($u);
$host = $p['host'] ?? null;
$user = $p['user'] ?? null;
$pass = $p['pass'] ?? null;
$db   = isset($p['path']) ? ltrim($p['path'], '/') : null;
$port = $p['port'] ?? 3306;

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "DB=" . $pdo->query("SELECT DATABASE()")->fetchColumn() . PHP_EOL;
echo "Tables=" . $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn() . PHP_EOL;
PHP
