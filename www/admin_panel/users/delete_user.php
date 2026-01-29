<?php
/** @var PDO $pdo */
session_start();
require_once("../../db_access.php");

// --------------------------------------------------
// Check whether user is logged in
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check role (admin only)

// session data comes from login.php / create_account.php
$user_role = $_SESSION["user_role"] ?? null;

if ($user_role !== "admin") {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Get user_id from POST
$target_user_id = $_POST["target_user_id"] ?? null;
if (!$target_user_id) {
    die("Invalid request.");
}
// --------------------------------------------------

// --------------------------------------------------
// Protection from deleting own user
if ($target_user_id == $user_id) {
    die("You cannot delete yourself.");
}
// --------------------------------------------------

// --------------------------------------------------
// Transaction as if something goes wrong, a roll back is executed
$pdo->beginTransaction();

try {

    // --------------------------------------------------
    // Load user to delete and set is_active to 0
    $sqlUser = "UPDATE users
        SET is_active = 0
        WHERE user_id = ?";

    // prepare SQL
    $stmtUser = $pdo->prepare($sqlUser);
    // bind values safely
    $stmtUser->execute([$target_user_id]);
    // --------------------------------------------------

    // --------------------------------------------------
    // Load articles from user to delete and set is_active to 0
    $sqlArticles = "
        UPDATE articles
        SET is_active = 0
        WHERE FK_user_id = ?
    ";
    $stmtArticles = $pdo->prepare($sqlArticles);
    $stmtArticles->execute([$target_user_id]);
    // --------------------------------------------------

    $pdo->commit();
    header("Location: users.php");
    exit;

} catch (Throwable $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    die("Error changing user status.");
}