<?php
/** @var PDO $pdo */
session_start();
require_once("db_access.php");

// --------------------------------------------------
// Check whether user is logged in
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check Role: All Articles Page only for Admin

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
// Load All Articles (title, timestamp)
$sql = "SELECT title, created_at, updated_at
        FROM articles
        WHERE is_active = 1;
        ";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely
$stmt->execute();
// get result; fetchAll, because we return all lines; FETCH_ASSOC returns the key and value
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- write title in Browser top -->
    <?php readfile(__DIR__ . "/assets/head.html"); ?>
    <title>My Articles</title>
</head>

<body>
<?php require_once("./assets/navbar.php"); ?>

<main class="container py-5">

    <h1>All Articles</h1>


    <!-- STRUCTURE
        $articles = [
            [
                "title" => "...",
                "summary" => "...",
                "created_at" => "...",
                "updated_at" => "..."
            ]
        ]
    -->

    <?php if (!$articles): ?>
        <p class="text-muted">No articles have been written yet.</p>
    <?php else: ?>

        <table class="table align-middle">
            <thead>
            <tr>
                <?php foreach (array_keys($articles[0]) as $column): ?>
                    <th><?= htmlspecialchars($column) ?></th>
                <?php endforeach; ?>
            </tr>

            <!-- OR
            <tr>
                <th>Title</th>
                <th>Created</th>
                <th>Updated</th>
            </tr>
            -->
            </thead>

            <tbody>
            <?php foreach ($articles as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row["title"]) ?></td>
                    <td><?= htmlspecialchars($row["created_at"]) ?></td>
                    <td><?= htmlspecialchars($row["updated_at"]) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</main>

<?php readfile("./assets/footer.html"); ?>
</body>
</html>
