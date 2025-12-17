<?php
/** @var PDO $pdo */
header("Content-Type: application/json");

require_once __DIR__ . "/db_access.php"; // loads $pdo

$q = isset($_GET["q"]) ? $_GET["q"] : "";

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT article_id, title, summary
    FROM articles
    WHERE MATCH(title, summary) AGAINST(:q IN NATURAL LANGUAGE MODE)
    LIMIT 10
");

$stmt->execute(['q' => $q]);
$rows = $stmt->fetchAll();

echo json_encode($rows);