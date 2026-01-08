<?php
require_once("db_access.php");
require_once("format_date.php");
?>

<?php
$db = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

$query = "
SELECT 
  a.article_id,
  a.title,
  a.created_at,
  ai.file_path,
  c.name
FROM articles AS a
JOIN categories c ON a.FK_category_id = c.category_id
JOIN article_images AS ai
  ON ai.FK_article_id = a.article_id
 AND ai.image_id = (
     SELECT MIN(image_id)
     FROM article_images
     WHERE FK_article_id = a.article_id
 )
WHERE a.article_id IN (4, 5, 6, 7);
"; // Select all necessary values of article and pictures, without duplicates
$stmt = $db->prepare($query);
$stmt->execute();
$stmt->bind_result($art_id, $art_title, $art_time, $art_img, $art_cat);
?>

<div class="row mb-2">
    <?php while($stmt->fetch()) { ?>
    <div class="col-md-6">
        <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
            <div class="col-auto d-none d-lg-block">
                <img src="./articles/<?php echo $art_img ?>" width="200" height="250" style="object-fit: cover;">
            </div>
            <div class="col p-4 d-flex flex-column position-static">
                <strong class="d-inline-block mb-2"><?php echo $art_cat ?></strong>
                <h3 class="mb-0"><?php echo $art_title ?></h3>
                <div class="mt-2 mb-1 text-body-secondary"><?php echo format_date($art_time)?></div>
                <a href="/articles/article.php?id=<?php echo $art_id; ?>" class="icon-link gap-1 icon-link-hover stretched-link">
                <svg class="bi" aria-hidden="true">
                    <use
                        xlink:href="./assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#chevron-right">
                    </use>
                </svg>
                </a>
            </div>
        </div>
        
    </div>
    <?php } ?>

<?php
$stmt->close();
$db->close();
?>