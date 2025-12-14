<?php
session_start();
require_once("db_access.php");

if (!array_intersect(["blogger", "admin"], $_SESSION["user_roles"] ?? [])) {
    http_response_code(403);
    die("Access denied");
}

$user_id = $_SESSION["user_id_logged_in"];

$stmt = $pdo->prepare("
    SELECT article_id, title, slug, created_at
    FROM articles
    WHERE FK_user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$blogs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("./assets/head.html"); ?>
    <title>My Articles</title>
</head>
<body>

<?php require "./assets/navbar.php"; ?>

<main class="container py-5">
    <h1>My Articles</h1>

    <?php if (!$blogs): ?>
        <p class="text-muted">You have not written any articles yet.</p>
    <?php else: ?>
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Title</th>
                <th>Created</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($blogs as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b["title"]) ?></td>
                    <td><?= $b["created_at"] ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary"
                           href="edit_article.php?slug=<?= urlencode($b["slug"]) ?>">
                            Edit
                        </a>
                        <a class="btn btn-sm btn-outline-danger"
                           href="delete_article.php?slug=<?= urlencode($b["slug"]) ?>"
                           onclick="return confirm('Delete this article?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

</body>
</html>