<?php
/** @var PDO $pdo */
session_start();
require_once("../../db_access.php");

// --------------------------------------------------
// Check login
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check role
$roles = $_SESSION["user_roles"] ?? [];
if (!in_array("admin", $roles)) {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Read POST data
$category_id = (int)($_POST['category_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($category_id <= 0 || $name === '' || $description === '') {
    die("Invalid input.");
}
// --------------------------------------------------

// --------------------------------------------------
// Update DB
$sql = "UPDATE categories
        SET name = ?, description = ?
        WHERE category_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$name, $description, $category_id]);
// --------------------------------------------------

// --------------------------------------------------
// Redirect
header("Location: categories.php");
exit;
// --------------------------------------------------