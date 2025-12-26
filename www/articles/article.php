<?php
/** @var PDO $pdo */
session_start();
require_once("../db_access.php");

// --------------------------------------------------
// $_GET["id"] comes from URL
$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    die("Missing article id.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load Article (title, author, timestamp) from article_id
$sql = "SELECT articles.*, users.username
        FROM articles
        LEFT JOIN users ON users.user_id = articles.FK_user_id
        WHERE articles.article_id = ?
        LIMIT 1";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely
$stmt->execute([$id]);
// get result; fetch, not fetchAll, because we only return a single line; FETCH_ASSOC returns the key and value
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// if no article found
if (!$article) {
    http_response_code(404);
    die("Article not found.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load Categories (categories which belong to the article)
$sql = "SELECT name
        FROM categories
        JOIN article_categories ON article_categories.FK_category_id = categories.category_id
        WHERE article_categories.FK_article_id = ?";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely (article_id we get from previous sql, line 29)
$stmt->execute([$article["article_id"]]);
// get result; fetchAll returns all results; FETCH_COLUMN only returns the value without the key
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
// --------------------------------------------------

// --------------------------------------------------
// Load Pictures
$sql = "SELECT file_path, alt_text
        FROM article_images
        WHERE FK_article_id = ?";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely (article_id we get from previous sql, line 29)
$stmt->execute([$article["article_id"]]);
// get result; fetchAll returns all results; FETCH_COLUMN only returns the value without the key
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- write title in Browser top -->
    <?php readfile("../assets/head.html"); ?>
    <title><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body>
<?php require_once("../assets/navbar.php"); ?>

<main class="container py-5">

    <!-- -------------------------------------------------- -->
    <!-- print title of article -->
    <h1><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <!-- -------------------------------------------------- -->

    <!-- -------------------------------------------------- -->
    <!-- grey text -->
    <p class="text-muted">
        <!-- print username and datetime -->
        By <?= htmlspecialchars($article["username"] ?? "Unknown", ENT_QUOTES, 'UTF-8') ?>
        • <?= htmlspecialchars($article["created_at"], ENT_QUOTES, 'UTF-8') ?>
    </p>
    <!-- -------------------------------------------------- -->

    <!-- -------------------------------------------------- -->
    <?php if (!empty($categories)): ?>
        <p>
            <strong>Categories:</strong>
            <!-- print each category -->
            <?php foreach ($categories as $cat): ?>
                <span class="badge bg-secondary"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>
    <!-- -------------------------------------------------- -->

    <!-- -------------------------------------------------- -->
    <?php if (!empty($images)): ?>
        <div class="my-5">
            <div class="row g-3">
                <!-- print each picture -->
                <?php foreach ($images as $img): ?>
                    <div class="col-12 col-md-6">
                        <img src="<?= htmlspecialchars($img['file_path'], ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($img['alt_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                             class="img-fluid rounded w-100">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <!-- -------------------------------------------------- -->

    <!-- -------------------------------------------------- -->
    <!-- print article content -->
    <div>
        <?= nl2br(htmlspecialchars($article["content"], ENT_QUOTES, 'UTF-8')) ?>
    </div>
    <!-- -------------------------------------------------- -->

</main>

<?php readfile("../assets/footer.html"); ?>
</body>
</html>