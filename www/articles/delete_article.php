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
// Check role (admin or blogger only)

// session data comes from login.php / create_account.php
$user_role = $_SESSION["user_role"] ?? null;

if (!in_array($user_role, ["admin", "blogger"], true)) {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Get user_id from POST
$article_id  = $_POST["target_article_id"] ?? null;
$redirect_to = $_POST["redirect_to"] ?? "index.php";

if (!$article_id) {
    die("Invalid request.");
}
// --------------------------------------------------

// --------------------------------------------------
// Transaction as if something goes wrong, a roll back is executed
$pdo->beginTransaction();

try {

    // --------------------------------------------------
    // Load articles from user to delete and set is_active to 0
    $sql = "
        UPDATE articles
        SET is_active = 0
        WHERE article_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$article_id]);
    // --------------------------------------------------

    $pdo->commit();
    header("Location: " . $redirect_to);
    exit;

} catch (Throwable $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    die("Error deleting article.");
}