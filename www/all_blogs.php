<?php
session_start();
require_once("db_access.php");

if (!in_array("admin", $_SESSION["user_roles"] ?? [])) {
    http_response_code(403);
    die("Admin only");
}

$stmt = $pdo->query("
    SELECT 
        a.title,
        a.slug,
        a.created_at,
        u.username
    FROM articles a
    JOIN users u ON u.user_id = a.FK_user_id
    ORDER BY a.created_at DESC
");

$blogs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("./assets/head.html"); ?>
    <title>All Blogs</title>
</head>
<body>

<?php require "./assets/navbar.php"; ?>

<main class="container py-5">
    <h1>All Blogs (Admin)</h1>

    <table class="table align-middle">
        <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($blogs as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b["title"]) ?></td>
                <td><?= htmlspecialchars($b["username"]) ?></td>
                <td><?= $b["created_at"] ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary"
                       href="edit_article.php?slug=<?= urlencode($b["slug"]) ?>">
                        Edit
                    </a>
                    <a class="btn btn-sm btn-outline-danger"
                       href="delete_article.php?slug=<?= urlencode($b["slug"]) ?>"
                       onclick="return confirm('Delete this article permanently?');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
