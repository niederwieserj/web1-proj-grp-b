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
// Check Role: Delete Categories only for Admin

// session data comes from login.php / create_account.php
$roles = $_SESSION["user_roles"] ?? [];

if (
    // check whether current user is admin
!in_array("admin", $roles)
) {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Get name from POST
$name = $_POST["name"] ?? null;
if (!$name) {
    die("Category must have a name.");
}
// --------------------------------------------------

// --------------------------------------------------
// Get category_id from POST
$description = $_POST["description"] ?? null;
if (!$description) {
    die("Category must have a description.");
}
// --------------------------------------------------

// --------------------------------------------------
// Transaction as if something goes wrong, a roll back is executed
$pdo->beginTransaction();

try {

    // --------------------------------------------------
    // Insert category into table
    $sqlCategories = "
        INSERT INTO categories (name, description)
        VALUES (?, ?)
        ";

    // prepare SQL
    $stmtUser = $pdo->prepare($sqlCategories);
    // bind values safely
    $stmtUser->execute([$name, $description]);
    // --------------------------------------------------


    $pdo->commit();
    header("Location: categories.php");
    exit;

} catch (Throwable $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    die("Error adding category.");
}