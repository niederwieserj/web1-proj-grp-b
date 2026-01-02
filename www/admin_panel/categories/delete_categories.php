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
// Get category_id from POST
$target_category_id = $_POST["target_category_id"] ?? null;
if (!$target_category_id) {
    die("Invalid request.");
}
// --------------------------------------------------

// --------------------------------------------------
// Transaction as if something goes wrong, a roll back is executed
$pdo->beginTransaction();

try {

    // --------------------------------------------------
    // Delete Category from table categories
    $sqlCategories = "
        DELETE FROM categories
        WHERE category_id = ?
        ";

    // prepare SQL
    $stmtUser = $pdo->prepare($sqlCategories);
    // bind values safely
    $stmtUser->execute([$target_category_id]);
    // --------------------------------------------------


    // NOT NEEDED, SINCE CATEGORIES IS ON CASCADE --> AUTOMATIC DELETE
    // --------------------------------------------------
    // Delete assigned category to article
    //$sqlArticleCategory = "
    //    DELETE FROM article_categories
    //    WHERE FK_category_id = ?
    //        ";
    //$stmtArticles = $pdo->prepare($sqlArticleCategory);
    //$stmtArticles->execute([$target_category_id]);
    // --------------------------------------------------

    $pdo->commit();
    header("Location: categories.php");
    exit;

} catch (Throwable $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    die("Error changing user status.");
}