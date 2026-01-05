<?php
/** @var PDO $pdo */
session_start();
require_once("../db_access.php");

// --------------------------------------------------
// Check login
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load return for correct redirect
$return = $_GET['return'] ?? 'articles.php';
// --------------------------------------------------

// --------------------------------------------------
// Load article
$article_id = (int)($_GET['id'] ?? 0);

$sql = "
    SELECT title, content, FK_category_id, FK_user_id
    FROM articles
    WHERE article_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article not found.");
}
// --------------------------------------------------

// --------------------------------------------------
// Permission check (admin OR owner)
$currentUserId = (int)$_SESSION["user_id_logged_in"];
$currentRole   = $_SESSION["user_role"] ?? null;

$isOwner = ($article["FK_user_id"] == $currentUserId);
$isAdmin = ($currentRole === "admin");

if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    die("You are not allowed to edit this article.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load categories
$sqlCategories = "
    SELECT category_id, name
    FROM categories
    ORDER BY name
";

$stmt = $pdo->query($sqlCategories);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --------------------------------------------------

// --------------------------------------------------
// Load images
$sqlImages = "
    SELECT image_id, file_path, alt_text
    FROM article_images
    WHERE FK_article_id = ?
";

$stmt = $pdo->prepare($sqlImages);
$stmt->execute([$article_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("../assets/head.html"); ?>
    <title>Edit Category</title>
</head>

<body>
<?php require_once("../assets/navbar.php"); ?>

<main class="container py-5">

    <h1 class="mb-4">Edit Article</h1>

    <form method="post" action="edit_articleToDB.php" enctype="multipart/form-data">

    <input type="hidden" name="article_id" value="<?= $article_id ?>">

        <!-- title -->
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control"
                    value="<?= htmlspecialchars($article['title']) ?>"
                    required
            >
        </div>

        <!-- content -->
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea
                    id="content"
                    name="content"
                    class="form-control"
                    rows="3"
                    required><?= htmlspecialchars($article['content']) ?></textarea>
        </div>

        <!-- categories -->
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <?php foreach ($categories as $cat): ?>
                    <option
                            value="<?= $cat['category_id'] ?>"
                            <?= $cat['category_id'] == $article['FK_category_id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- images -->
        <h3 class="mt-4">Images</h3>

        <?php if ($images): ?>
            <?php foreach ($images as $img): ?>
                <div class="border p-3 mb-3">
                    <img
                            src="<?= htmlspecialchars($img['file_path']) ?>"
                            class="img-fluid mb-2"
                            style="max-height:150px"
                    >

                    <div class="form-check">
                        <input
                                class="form-check-input"
                                type="checkbox"
                                name="delete_images[]"
                                value="<?= $img['image_id'] ?>"
                                id="img<?= $img['image_id'] ?>"
                        >
                        <label class="form-check-label" for="img<?= $img['image_id'] ?>">
                            Delete image
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No images attached.</p>
        <?php endif; ?>

        <h4 class="mt-4">Add new images</h4>

        <div class="mb-3">
            <input
                    type="file"
                    name="new_images[]"
                    class="form-control"
                    accept="image/*"
                    multiple
            >
        </div>





        <input type="hidden" name="return" value="<?= htmlspecialchars($return) ?>">


        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="<?= htmlspecialchars($return) ?>" class="btn btn-secondary">Cancel</a>
        </div>

    </form>

</main>

<?php readfile("../assets/footer.html"); ?>
</body>
</html>