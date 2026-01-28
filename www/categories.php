<?php
session_start();

require_once("db_access.php");
require_once("./display_content/format_date.php");

$category_id = $_GET["category"];

if (empty($category_id)) {
  $category_id = 1;
}

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
    WHERE a.FK_CATEGORY_ID = ?
    AND a.is_active = 1
    ORDER BY created_at DESC;
  ";

$stmt = $pdo->prepare($query);
$stmt->execute([$category_id]);
//$stmt->bind_result($art_id, $art_title, $art_time);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "
  SELECT 
  name,
  description
  FROM categories
  WHERE
  category_id = ?;
  ";

$stmt = $pdo->prepare($query);
$stmt->execute([$category_id]);
//$stmt->bind_result($art_id, $art_title, $art_time);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
$_SESSION["current_page"] = $category["name"];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
  <?php readfile("./assets/head.html"); ?>
  <link href="./assets/css/blog.css" rel="stylesheet" />

  <title><?php echo $category["name"] ?></title>
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">

  <?php
  require_once("./assets/navbar.php");
  ?>

  <main class="container" style="flex: 1;">

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
        <div class="position-sticky" style="top: 2rem">
          <div class="p-4 mt-3 mb-3 border border-secondary rounded">
            <h4 class="fst-italic">About</h4>
            <p class="mb-0">
              <?php echo $category["description"]; ?>
            </p>
          </div>
        </div>
      </div>

    </div>

    <div class="row g-5">
      <div class="col-md-8">
      </div>
      <div class="col-md-4">

      </div>
    </div>
  </main>
  <?php readfile("./assets/footer.html"); ?>
</body>

</html>