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
// Check Role: Categories only for Admin

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
// Load All Categories (title, timestamp) from users
$sql = "SELECT category_id, name, description
        FROM categories
        ";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely
$stmt->execute();
// get result; fetchAll, because we return all lines; FETCH_ASSOC returns the key and value
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- write title in Browser top -->
    <?php readfile("../../assets/head.html"); ?>
    <title>Categories</title>
</head>

<body>
<?php require_once("../../assets/navbar.php"); ?>

<main class="container py-5">

    <h1>Categories</h1>


    <!-- STRUCTURE
        $users = [
            [
                "user_id" => "...",
                "email" => "...",
                "created_at" => "...",
                "updated_at" => "..."
            ]
        ]
    -->

    <table class="table align-middle">
        <thead>

            <!--
            <tr>
                <?php foreach (array_keys($categories[0]) as $column): ?>
                    <th><?= htmlspecialchars($column) ?></th>
                <?php endforeach; ?>
            </tr>
            -->

            <tr>
                <th>Name</th>
                <th>Description</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($categories as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row["name"]) ?></td>
                <td><?= htmlspecialchars($row["description"]) ?></td>

                <td>
                    <form method="post" action="delete_categories.php" onsubmit="return confirm('Delete Category?');">

                        <!-- send value user_id via POST to delete_user -->
                        <input type="hidden" name="target_category_id" value="<?= (int)$row["category_id"] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</main>

<?php readfile("../../assets/footer.html"); ?>
</body>
</html>