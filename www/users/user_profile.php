<?php
/** @var PDO $pdo */
session_start();
$_SESSION["current_page"] = "";

require_once("../db_access.php");
require_once("../display_content/format_date.php");

// Get user ID and pagination number from URL
$user_id = (int) ($_GET["id"] ?? 0);
$pagination_nr = (int) ($_GET["page"] ?? 1);

if (!isset($_GET["page"])) {
    // Set page number if not not in URL
    header('Location: '.$_SERVER['PHP_SELF']."?id=".$user_id."&page=1");
}

// --------------------------------------------------
// Check role (admin or blogger only)

// session data comes from login.php / create_account.php
$user_role = $_SESSION["user_role"] ?? null;
$user_id_logged_in = $_SESSION["user_id_logged_in"] ?? null;

// --------------------------------------------------

// --------------------------------------------------
// Load User data from users
$sql = "SELECT users.user_id, users.username, users.email, users.created_at, user_image.file_path, user_image.alt_text
        FROM users
        LEFT JOIN user_image
            ON users.user_id = user_image.FK_user_id
        WHERE user_id = ?
        AND is_active = 1
        LIMIT 1;
";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely
$stmt->execute([$user_id]);
// get result; fetch, because we return a single lines; FETCH_ASSOC returns the key and value
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// --------------------------------------------------

// Get number of user's articles for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM articles WHERE articles.FK_USER_ID = ?;");
$stmt->execute([$user_id]);
$article_count = $stmt->fetch(PDO::FETCH_ASSOC)["count"];

$articles_per_page = 3;
$total_pages = $article_count / $articles_per_page;

if ($total_pages < 1) {
    $total_pages = 1;
}

if ($pagination_nr > $total_pages) {
    $pagination_nr = $total_pages;
    header('Location: '.$_SERVER['PHP_SELF']."?id=".$user_id."&page=".$pagination_nr);
} else if ($pagination_nr < 1) {
    $pagination_nr = 1;
    header('Location: '.$_SERVER['PHP_SELF']."?id=".$user_id."&page=".$pagination_nr);
}

$articles_idx_start = ($pagination_nr - 1) * $articles_per_page;

// Get articles of user from DB
$query = "
  SELECT 
    a.article_id,
    a.title,
    a.created_at,
    ai.file_path
    FROM articles AS a
    LEFT JOIN article_images AS ai
    ON ai.FK_article_id = a.article_id
    AND ai.image_id = (
        SELECT MIN(image_id)
        FROM article_images
        WHERE FK_article_id = a.article_id
    )
    WHERE a.FK_USER_ID = :user_id
    AND a.is_active = 1
    ORDER BY created_at DESC
    LIMIT :start,:count;
  ";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':start', $articles_idx_start, PDO::PARAM_INT);
$stmt->bindParam(':count', $articles_per_page, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- write title in Browser top -->
    <?php readfile("../assets/head.html"); ?>
    <title><?php echo $user["username"] . "'s Blog";?></title>
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">
    <?php require_once("../assets/navbar.php"); ?>

    <main class="container d-flex flex-column py-3" style="flex: 1;">

            <?php if (!empty($user["username"])) { ?>

                <!-- User info -->
                <div class="row">
                    <div class="col-2"></div>
                    <!-- Profilbild -->
                    <div class="col-8 d-flex flex-row align-items-center mb-4">
                        <svg class="bi mx-3" aria-hidden="true" width="64" height="64">
                            <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#person-circle">
                            </use>
                        </svg>

                        <div class="me-auto">
                            <h4 class="m-0 pt-1"><?= htmlspecialchars($user["username"]) ?></h4>
                            <p class="fst-italic m-0 pt-1">Blogs since <?php echo format_date(htmlspecialchars($user["created_at"])) ?></p>
                        </div>

                        <?php if ($user_id === $user_id_logged_in || $user_role === "admin") { ?>
                            <a href="edit_user.php?id=<?= (int) $user_id ?>" class="btn btn-outline-primary float-end mt-3">
                                <svg class="bi" aria-hidden="true" width="20" height="20">
                                    <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#pencil">
                                    </use>
                                </svg>
                            </a>
                        <?php } ?>
                        
                    </div>
                    <div class="col-2">
                        
                    </div>
                </div>

                <!-- Blog posts of user -->
                <div class="row mt-4">
                    <div class="col-2"></div>
                    <div class="col-8">
                        <h1 class="mb-4"><?= htmlspecialchars($user["username"]) ?>'s blog posts</h1>
                        <?php foreach ($articles as $article) { ?>
                            <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top position-relative" href="/articles/article.php?id=<?php echo $article["article_id"]; ?>">
                                <?php if (!empty($article["file_path"])) { ?>
                                <img src="/articles/<?php echo $article["file_path"] ?>" width="120" height="100" style="object-fit: cover;" class="rounded">
                                <?php } else { ?>
                                <div class="card border border-primary-subtle border-3" style="width: 120px; height: 100px;">
                                </div>
                                <?php } ?>
                                <div class="col-lg-8">
                                <h6 class="mb-0">
                                    <?php echo $article["title"]; ?>
                                </h6>
                                <small class="text-body-secondary">
                                    <?php echo format_date($article["created_at"]); ?>
                                </small>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                    <div class="col-2"></div>
                </div>

                <!-- Pagination -->
                <div class="row mt-auto">
                    <div class="col-2"></div>
                    <div class="col-8 d-flex flex-column align-items-center">
                        <nav aria-label="Blog post navigation">
                        <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $_SERVER['PHP_SELF']."?id=".$user_id."&page=".($pagination_nr-1); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 0; $i < $total_pages; $i++) { ?>
                                <li class="page-item"><a class="page-link" href="<?php echo $_SERVER['PHP_SELF']."?id=".$user_id."&page=".($i+1); ?>"><?php echo $i+1; ?></a></li>
                            <?php } ?>
                            
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $_SERVER['PHP_SELF']."?id=".$user_id."&page=".($pagination_nr+1); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                        </nav>
                    </div>
                    <div class="col-2"></div>
                </div>

            <?php } else {
                echo "User not found.";
            }
            ?>

    </main>

    <?php readfile("../assets/footer.html"); ?>
</body>

</html>