<?php
/** @var PDO $pdo */
session_start();
$_SESSION["current_page"] = "";

require_once("./db_access.php");
require_once("./display_content/format_date.php");

// ----- Check search parameters -----
$user_id = (int) ($_GET["id"] ?? 0);
$pagination_nr = (int) ($_GET["page"] ?? 1);

if (!isset($_GET["username"]) || !isset($_GET["title"]) || !isset($_GET["category"]) || !isset($_GET["dateFrom"]) || !isset($_GET["dateTo"]) || !isset($_GET["orderBy"]) || !isset($_GET["sortOrder"]) || !isset($_GET["limit"])) {
    // Default values
    $username = "";
    $title = "";
    $category = "";
    $dateFrom = "";
    $dateTo = "";
    $orderBy = "date";
    $sortOrder = "DESC";
    $limit = "10";

    // Redirect to search page with standard parameters if any value not set in URL
    header('Location: ' . $_SERVER['PHP_SELF'] . "?username=&title=&category=&dateFrom=&dateTo=&orderBy=".$orderBy."&sortOrder=".$sortOrder."&limit=".$limit);
}

$username = $_GET["username"];
$title = $_GET["title"];
$category = $_GET["category"];
$dateFrom = $_GET["dateFrom"];
$dateTo = $_GET["dateTo"];
$orderBy = $_GET["orderBy"];
$sortOrder = $_GET["sortOrder"];
$limit = $_GET["limit"];

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
$total_pages = ceil($article_count / $articles_per_page);

if ($total_pages < 1) {
    $total_pages = 1;
}

if ($pagination_nr > $total_pages) {
    $pagination_nr = $total_pages;
    header('Location: ' . $_SERVER['PHP_SELF'] . "?id=" . $user_id . "&page=" . $pagination_nr);
} else if ($pagination_nr < 1) {
    $pagination_nr = 1;
    header('Location: ' . $_SERVER['PHP_SELF'] . "?id=" . $user_id . "&page=" . $pagination_nr);
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
    <?php readfile("./assets/head.html"); ?>
    <title>Search</title>
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">
    <?php require_once("./assets/navbar.php"); ?>

    <main class="container d-flex flex-column py-3" style="flex: 1;">
        <div class="row">
            <form action="/search.php" method="get" class="card col d-flex flex-wrap flex-row align-items-center gap-2 p-3">
                <div class="input-group" style="max-width: 15%;">
                    <span class="input-group-text" id="basic-addon1">@</span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
                </div>

                <div class="input-group" style="max-width: 25%;">
                    <span class="input-group-text" id="basic-addon2">
                        <svg class="bi" aria-hidden="true" width="1.5rem" height="1.5rem">
                            <use xlink:href="./assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#alphabet">
                            </use>
                        </svg>
                    </span>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Title" aria-label="Title" aria-describedby="basic-addon2">
                </div>

                <label for="category">Category</label>
                <select class="form-select" id="category" name="category" style="max-width: 15%;" aria-label="Choose category">
                    <option value="" selected>Choose a category</option>
                    <option value="Lifestyle">Lifestyle</option>
                    <option value="Travel">Travel</option>
                    <option value="Food">Food</option>
                    <option value="Technology">Technology</option>
                    <option value="Health">Health</option>
                </select>

                <label for="dateFrom">From date</label>
                <input type="date" id="dateFrom" name="dateFrom" />

                <label for="dateTo">To date</label>
                <input type="date" id="dateTo" name="dateTo" <?php if(!empty($dateTo)) { echo "value=\"$dateTo\""; } ?> />

                <label for="orderBy">Order by</label>
                <select class="form-select" id="orderBy" name="orderBy" style="max-width: 10%;" aria-label="Choose order by">
                    <option value="1">Date</option>
                    <option value="2">Author</option>
                    <option value="3">Title</option>
                    <option value="4">Category</option>
                </select>

                <label for="sortOrder">Sort order</label>
                <select class="form-select" id="sortOrder" name="sortOrder" style="max-width: 12%;" aria-label="Choose sort order">
                    <option value="ASC">Ascending</option>
                    <option value="DESC">Descending</option>
                </select>

                <label for="limit">No. results</label>
                <select class="form-select" id="limit" name="limit" style="max-width: 10%;" aria-label="Choose no. of results">
                    <option value="10" <?php if((int) $limit == 10) { echo "selected"; } ?>>10</option>
                    <option value="20" <?php if((int) $limit == 20) { echo "selected"; } ?>>20</option>
                    <option value="50" <?php if((int) $limit == 50) { echo "selected"; } ?>>50</option>
                    <option value="100" <?php if((int) $limit == 100) { echo "selected"; } ?>>100</option>
                </select>

                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>


    </main>

    <?php readfile("./assets/footer.html"); ?>
</body>

</html>