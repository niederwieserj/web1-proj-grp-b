<?php
/** @var PDO $pdo */
session_start();
require_once("db_access.php");

if (!isset($_GET["slug"])) {
    http_response_code(400);
    die("Missing slug.");
}

$slug = $_GET["slug"];

// ----------------------------
// Load Articles
// ----------------------------
$sql = "SELECT a.*, u.username 
        FROM articles a
        LEFT JOIN users u ON u.user_id = a.FK_user_id
        WHERE a.slug = ?
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    die("Article not found.");
}

// ----------------------------
// Load Categories
// ----------------------------
$sql = "SELECT c.name
        FROM categories c
        JOIN article_categories ac ON ac.FK_category_id = c.category_id
        WHERE ac.FK_article_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$article["article_id"]]);
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// ----------------------------
// Load Pictures
// ----------------------------
$sql = "SELECT file_path, alt_text
        FROM article_images
        WHERE FK_article_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$article["article_id"]]);
$images = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("./assets/head.html"); ?>
</head>

<body>
<?php require_once("./assets/navbar.php"); ?>

<main class="container py-5">

    <h1><?= htmlspecialchars($article["title"]) ?></h1>

    <p class="text-muted">
        By <?= htmlspecialchars($article["username"] ?? "Unknown") ?>
        • <?= $article["created_at"] ?>
    </p>

    <?php if (!empty($categories)): ?>
        <p>
            <strong>Categories:</strong>
            <?php foreach ($categories as $cat): ?>
                <span class="badge bg-secondary"><?= htmlspecialchars($cat) ?></span>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($images)): ?>
        <div class="my-4">
            <?php foreach ($images as $img): ?>
                <img src="/<?= htmlspecialchars($img["file_path"]) ?>"
                     alt="<?= htmlspecialchars($img["alt_text"] ?? "") ?>"
                     class="img-fluid rounded mb-3">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <?= nl2br(htmlspecialchars($article["content"])) ?>
    </div>

</main>

<?php readfile("./assets/footer.html"); ?>
</body>
</html>