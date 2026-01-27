<?php
    /** @var PDO $pdo */
    session_start();
    $_SESSION["current_page"] = "";

    require_once("./db_access.php");
    require_once("./display_content/format_date.php");

    // ----- Check search parameters -----
    if (!isset($_GET["username"]) || !isset($_GET["title"]) || !isset($_GET["category"]) || !isset($_GET["dateFrom"]) || !isset($_GET["dateTo"]) || !isset($_GET["orderBy"]) || !isset($_GET["sortOrder"]) || !isset($_GET["limit"])) {
        // Default values
        $username = "";
        $title = "";
        $category = "";
        $date_from = "";
        $date_to = "";
        $order_by = "date";
        $sort_order = "DESC";
        $limit = "10";

        // Redirect to search page with standard parameters if any value not set in URL
        header('Location: ' . $_SERVER['PHP_SELF'] . "?username=&title=&category=&dateFrom=&dateTo=&orderBy=".$order_by."&sortOrder=".$sort_order."&limit=".$limit);
    }

    // Load All Categories
    $sqlAllCategories = "SELECT category_id, name, description FROM categories";
    $stmt = $pdo->prepare($sqlAllCategories);
    $stmt->execute();
    $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $username = $_GET["username"];
    $title = $_GET["title"];

    $category = $_GET["category"];

    $foundCategory = false;

    foreach($allCategories as $row) {
        if ($row["name"] == $category) {
            $foundCategory = true;
            break;
        }
    }

    if (!$foundCategory) {
        $category = "";
    }

    $date_from = $_GET["dateFrom"];

    if(!DateTime::createFromFormat('Y-m-d', $date_from)) {
        $date_from = "";
    }

    $date_to = $_GET["dateTo"];

    if(!DateTime::createFromFormat('Y-m-d', $date_to)) {
        $date_to = "";
    }

    $order_by = $_GET["orderBy"];

    if(!in_array($order_by, ["Date", "Username", "Title", "Category"])) {
        $order_by = "Date";
    }

    $sort_order = $_GET["sortOrder"];

    if($sort_order != "ASC" && $sort_order != "DESC") {
        $sort_order = "DESC";
    }

    $limit = $_GET["limit"];

    if(!is_numeric($limit)) {
        $limit = 10;
    }

    // Modify values to fit SQL syntax
    $query_username = "%" . $username . "%";
    $query_title = "%" . $title . "%";
    $query_category = "%" . $category . "%";
    $query_order_by = "";

    switch ($order_by) {
        case "Date":
            $query_order_by = "a.created_at";
            break;
        case "Username":
            $query_order_by = "users.username";
            break;
        case "Title":
            $query_order_by = "a.title";
            break;
        case "Category":
            $query_order_by = "categories.name";
            break;
        default:
            $query_order_by = "a.created_at";
            break;
    }

// Search articles in DB
$query = '
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
    JOIN users
	    ON FK_user_id = user_id
    JOIN categories
        on FK_category_id = category_id
    WHERE users.username LIKE :username
        AND a.title LIKE :title
        AND categories.name LIKE :category
        AND DATE(a.created_at) >= :date_from
        AND DATE(a.created_at) <= :date_to
        AND a.is_active = 1
    ORDER BY ' . $query_order_by . ' ' . $sort_order . '
    LIMIT :limit;
  '; // It's not optimal to insert variables directly into the query string, but should be safe due to checking. Without direct injection, it is not possible to get rid of the apostrophes which break the query.

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $query_username, PDO::PARAM_STR);
    $stmt->bindParam(':title', $query_title, PDO::PARAM_STR);
    $stmt->bindParam(':category', $query_category, PDO::PARAM_STR);
    $stmt->bindParam(':date_from', $date_from, PDO::PARAM_STR);
    $stmt->bindParam(':date_to', $date_to, PDO::PARAM_STR);
    //$stmt->bindParam(':order_by', $query_order_by, PDO::PARAM_STR_CHAR);
    //$stmt->bindParam(':sort_order', $sort_order, PDO::PARAM_STR_CHAR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
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
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" value="<?php echo $username ?>">
                </div>

                <div class="input-group" style="max-width: 25%;">
                    <span class="input-group-text" id="basic-addon2">
                        <svg class="bi" aria-hidden="true" width="1.5rem" height="1.5rem">
                            <use xlink:href="./assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#alphabet">
                            </use>
                        </svg>
                    </span>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Title" aria-label="Title" aria-describedby="basic-addon2" value="<?php echo $title ?>">
                </div>

                <label for="category">Category</label>
                <select class="form-select" id="category" name="category" style="max-width: 15%;" aria-label="Choose category">
                    <option value="" <?php if($category == "") { echo "selected"; } ?>>Choose a category</option>
                    <?php foreach($allCategories as $row) {
                        echo '<option value="'. $row["name"] .'"';
                        
                        if($category == $row["name"]) {
                            echo "selected";
                        }
                        
                        echo '>'. $row["name"] .'</option>';
                    } ?>
                </select>

                <label for="dateFrom">From date</label>
                <input type="date" id="dateFrom" name="dateFrom" <?php if(!empty($date_from)) { echo "value=\"$date_from\""; } ?> />

                <label for="dateTo">To date</label>
                <input type="date" id="dateTo" name="dateTo" <?php if(!empty($date_to)) { echo "value=\"$date_to\""; } ?> />

                <label for="orderBy">Order by</label>
                <select class="form-select" id="orderBy" name="orderBy" style="max-width: 10%;" aria-label="Choose order by">
                    <option value="Date" <?php if($order_by == "Date") { echo "selected"; } ?>>Date</option>
                    <option value="Username" <?php if($order_by == "Username") { echo "selected"; } ?>>Username</option>
                    <option value="Title" <?php if($order_by == "Title") { echo "selected"; } ?>>Title</option>
                    <option value="Category" <?php if($order_by == "Category") { echo "selected"; } ?>>Category</option>
                </select>

                <label for="sortOrder">Sort order</label>
                <select class="form-select" id="sortOrder" name="sortOrder" style="max-width: 12%;" aria-label="Choose sort order">
                    <option value="ASC" <?php if($sort_order == "ASC") { echo "selected"; } ?>>Ascending</option>
                    <option value="DESC" <?php if($sort_order == "DESC") { echo "selected"; } ?>>Descending</option>
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

        <div class="row">
      <div class="col-8">
      <?php foreach ($articles as $article) { ?>
        
          <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top position-relative" href="/articles/article.php?id=<?php echo $article["article_id"]; ?>">
            <?php if (!empty($article["file_path"])) { ?>
              <img src="./articles/<?php echo $article["file_path"] ?>" width="120" height="100" style="object-fit: cover;" class="rounded">
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
      <div class="col-4">
      </div>
      </div>


    </main>

    <?php readfile("./assets/footer.html"); ?>
</body>

</html>