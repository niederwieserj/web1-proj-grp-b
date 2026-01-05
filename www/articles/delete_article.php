<?php
/** @var PDO $pdo */
session_start();
require_once("../db_access.php");

// --------------------------------------------------
// Check whether user is logged in
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Read POST data
$article_id  = (int)($_POST["target_article_id"] ?? 0);
$redirect_to = $_POST["redirect_to"] ?? "index.php";

if ($article_id <= 0) {
    die("Invalid request.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load owner
$stmt = $pdo->prepare("
    SELECT FK_user_id
    FROM articles
    WHERE article_id = ?
");
$stmt->execute([$article_id]);
$ownerId = $stmt->fetchColumn();

if (!$ownerId) {
    die("Article not found.");
}
// --------------------------------------------------

// --------------------------------------------------
// Permission check (admin OR owner)
$currentUserId = (int)$_SESSION["user_id_logged_in"];
$currentRole   = $_SESSION["user_role"] ?? null;

$isOwner = ($ownerId == $currentUserId);
$isAdmin = ($currentRole === "admin");

if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    die("You are not allowed to delete this article.");
}
// --------------------------------------------------

// --------------------------------------------------
// Soft delete article
$sql = "
    UPDATE articles
    SET is_active = 0
    WHERE article_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$article_id]);
// --------------------------------------------------

header("Location: " . $redirect_to);
exit;