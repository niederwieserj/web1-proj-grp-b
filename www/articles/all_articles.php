<?php
/** @var PDO $pdo */
session_start();
$_SESSION["current_page"] = "";

require_once("../db_access.php");
require_once("../display_content/format_date.php");

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
// Load All Articles (title, timestamp)

$sql = "SELECT users.user_id, users.username, articles.article_id, articles.title, articles.created_at, articles.updated_at
        FROM articles
        JOIN users
            ON articles.FK_user_id = users.user_id
        WHERE articles.is_active = 1
        ORDER BY articles.created_at DESC;
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
    <?php readfile("../assets/head.html"); ?>
    <title>Articles</title>
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">
<?php require_once("../assets/navbar.php"); ?>

<main class="container" style="flex: 1;">

    <h1  class="mt-4 mb-4">All Articles</h1>


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

        <table class="table table-hover align-middle">
            <thead>
            <tr>
                <th>Username</th>
                <th>Title</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>

            <!-- OR
                <tr>
                    <?php foreach (array_keys($articles[0]) as $column): ?>
                        <th><?= htmlspecialchars($column) ?></th>
                    <?php endforeach; ?>
                </tr>
                -->

            </thead>

            <tbody>
            <?php foreach ($articles as $row): ?>
                <tr>
                    <td>
                        <a href="/users/user_profile.php?id=<?= htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($row["username"]) ?>
                        </a>
                    </td>
                    <td><a href="article.php?id=<?= (int)$row['article_id'] ?>"  target="_blank">
                        <?= htmlspecialchars($row["title"]) ?>
                        </a>
                    </td>
                    <td><?php echo format_date(htmlspecialchars($row["created_at"]))?></td>
                    <td><?php echo format_date(htmlspecialchars($row["updated_at"]))?></td>

                    <td>
                        <a href="edit_article.php?id=<?= (int)$row['article_id'] ?>&return=all_articles.php" class="btn btn-outline-primary btn-sm">
                            <svg class="bi mx-1" aria-hidden="true" width="16" height="16">
                                <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#pencil">
                                </use>
                            </svg>
                        </a>
                    </td>

                    <td>
                        <form method="post" action="delete_article.php" onsubmit="return confirm('Delete Article?');">
                            <!-- send value article_id via POST to delete_article -->
                            <input type="hidden" name="target_article_id" value="<?= (int)$row["article_id"] ?>">
                            <input type="hidden" name="redirect_to" value="all_articles.php">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <svg class="bi mx-1" aria-hidden="true" width="16" height="16">
                                    <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#trash">
                                    </use>
                                </svg>
                            </button>
                        </form>
                    </td>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</main>

<?php readfile("../assets/footer.html"); ?>
</body>
</html>
