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
// Check role (admin only)

// session data comes from login.php / create_account.php
$user_role = $_SESSION["user_role"] ?? null;

if ($user_role !== "admin") {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- write title in Browser top -->
    <?php readfile("../assets/head.html"); ?>
    <title>Admin Panel</title>
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">
<?php require_once("../assets/navbar.php"); ?>

<main class="container py-5" style="flex: 1;">
    <h1>Admin Panel</h1>

    <div class="list-group">
        <a href="users/users.php" class="list-group-item list-group-item-action">
            Manage Users
        </a>
        <a href="categories/categories.php" class="list-group-item list-group-item-action">
            Manage Categories
        </a>
    </div>
</main>

<?php readfile("../assets/footer.html"); ?>
</body>
</html>
