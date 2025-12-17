<?php
/** @var PDO $pdo */
session_start();
require_once("db_access.php");

if (!in_array("admin", $_SESSION["user_roles"])) {
    die("Admin only");
}

$user_id = $_POST["user_id"] ?? null;

if (!$user_id) die("Invalid user");

$stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
$stmt->execute([$user_id]);

header("Location: admin_panel.php");
exit;