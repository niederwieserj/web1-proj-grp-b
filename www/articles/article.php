<?php
/** @var PDO $pdo */
session_start();
require_once("../db_access.php");
require_once("../display_content/format_date.php");

// --------------------------------------------------
// $_GET["id"] comes from URL
$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    die("Missing article id.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load Article (title, author, timestamp) from article_id + user_image
$sql = "SELECT 
            articles.*,
            users.username,
            users.user_id,
            user_image.file_path   AS profile_image_path,
            user_image.alt_text    AS profile_image_alt
        FROM articles
        LEFT JOIN users 
            ON users.user_id = articles.FK_user_id
        LEFT JOIN user_image
            ON user_image.FK_user_id = users.user_id
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
$sql = "
    SELECT categories.name
    FROM articles
    JOIN categories ON categories.category_id = articles.FK_category_id
    WHERE articles.article_id = ?
";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely (article_id we get from previous sql, line 29)
$stmt->execute([$article["article_id"]]);
// get result; fetch returns a single line
$category = $stmt->fetchColumn();

$_SESSION["current_page"] = $category;
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

// --------------------------------------------------
// Permission check for Edit button (admin OR owner)

$currentUserId = $_SESSION["user_id_logged_in"] ?? null;
$currentRole = $_SESSION["user_role"] ?? null;

$isOwner = ($currentUserId && $article["FK_user_id"] == $currentUserId);
$isAdmin = ($currentRole === "admin");
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
        <!--
        <?php if (!empty($images)): ?>
            <?php $img = $images[0] ?>
                <img src="<?= htmlspecialchars($img['file_path'], ENT_QUOTES, 'UTF-8') ?>" class="d-block w-100" alt="<?= htmlspecialchars($img['alt_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        -->

        <!-- Title of article -->
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8 col-sm-12">
                <h1><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <div class="col-md-2"></div>
        </div>

        <!-- Article metadata -->
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <div class="d-flex flex-row flex-wrap gap-2 justify-content-left py-5">
                    <!-- User badge -->
                    <span class="badge d-flex align-items-center p-1 pe-2 text-success-emphasis bg-success-subtle border border-success-subtle rounded-pill">

                        <?php if (!empty($article['profile_image_path'])): ?>
                            <!-- Show uploaded profile picture -->
                            <img
                                    src="<?= htmlspecialchars($article['profile_image_path'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($article['profile_image_alt'] ?? 'Profile image', ENT_QUOTES, 'UTF-8') ?>"
                                    class="rounded-circle me-1"
                                    width="24" height="24"
                                    style="object-fit: cover;"
                            >
                        <?php else: ?>
                            <!-- Fallback icon if no profile image -->
                            <svg class="bi me-1" aria-hidden="true" width="24" height="24">
                                <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#person-circle"></use>
                            </svg>
                        <?php endif; ?>

                        <a href="../users/user_profile.php?id=<?= htmlspecialchars($article['user_id'], ENT_QUOTES, 'UTF-8') ?>"
                           class="text-reset text-decoration-none">
                            <?= htmlspecialchars($article["username"] ?? "Unknown", ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </span>


                    <!-- Category badge -->
                    <?php if ($category): ?>
                        <span class="badge d-flex align-items-center p-1 pe-2 text-success-emphasis bg-success-subtle border border-success-subtle rounded-pill">
                            <svg class="bi mx-1" aria-hidden="true" width="20" height="20">
                                <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#tag">
                                </use>
                            </svg>
                            <a href="/categories.php?category=<?= htmlspecialchars($article['FK_category_id'], ENT_QUOTES, 'UTF-8') ?>" class="text-reset text-decoration-none">
                                <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </span>
                    <?php endif; ?>

                    <!-- Date badge -->
                    <span class="badge d-flex align-items-center p-1 pe-2 text-success-emphasis bg-success-subtle border border-success-subtle rounded-pill">
                        <svg class="bi mx-1" aria-hidden="true" width="20" height="20">
                            <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#calendar-event">
                            </use>
                        </svg>
                        <?php echo format_date($article["created_at"]);?>
                    </span>

                    <!-- Edit article button -->
                    <?php if ($isAdmin || $isOwner): ?>
                        <div>
                            <a href="edit_article.php?id=<?= (int) $article['article_id'] ?>&return=article.php?id=<?= (int) $article['article_id'] ?>" class="btn btn-sm btn-outline-secondary border-0">
                                <svg class="bi" aria-hidden="true" width="20" height="20">
                                    <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#pencil">
                                    </use>
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>

        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8 col-sm-12">
                <?= nl2br(htmlspecialchars($article["content"], ENT_QUOTES, 'UTF-8')) ?>
            </div>
            <div class="col-md-2"></div>
        </div>

        <!-- Picture carousel -->
        <?php if (!empty($images)): ?>
            <div class="row mt-4">
                <div class="col-md-2"></div>
                <div class="col-md-8 col-sm-12">
                    <div id="carouselExample" class="carousel slide">
                        <div class="carousel-inner">
                            <?php $index = 0; ?>
                            <?php foreach ($images as $img): ?>
                                <div class="carousel-item <?php if($index == 0) { echo "active"; } ?>">
                                    <img src="<?= htmlspecialchars($img['file_path'], ENT_QUOTES, 'UTF-8') ?>" class="d-block w-100" alt="<?= htmlspecialchars($img['alt_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php $index++; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        <?php endif; ?>

    </main>

    <?php readfile("../assets/footer.html"); ?>
</body>

</html>