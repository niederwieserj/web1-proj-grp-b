<?php require_once("db_access.php"); ?>

<?php
$db = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

// Vegetable article
$query = "SELECT article_id, title, articles.created_at, file_path, categories.name FROM articles INNER JOIN article_images ON article_id=FK_article_id INNER JOIN categories ON FK_category_id=category_id WHERE article_id IN (4, 5, 6);";
$stmt = $db->prepare($query);
$stmt->execute();
$stmt->bind_result($art_id, $art_title, $art_time, $art_img, $art_cat);
?>

<div class="row mb-2">
    <?php while($stmt->fetch()) { ?>
    <div class="col-md-6">
        <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
            <div class="col p-4 d-flex flex-column position-static">
                <strong class="d-inline-block mb-2"><?php echo $art_cat ?></strong>
                <h3 class="mb-0"><?php echo $art_title ?></h3>
                <div class="mb-1 text-body-secondary"><?php echo $art_time ?></div>
                <p class="card-text mb-auto">
                    
                    <a href="/articles/article.php?id=5" class="icon-link gap-1 icon-link-hover stretched-link">
                        <svg class="bi" aria-hidden="true">
                            <use
                                xlink:href="./assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#chevron-right">
                            </use>
                        </svg>
                    </a>
                </p>

            </div>
            <div class="col-auto d-none d-lg-block">
                <img src="./articles/<?php echo $art_img ?>" width="200" height="250" style="object-fit: cover;">
            </div>
        </div>
    </div>
    <?php } ?>

<?php
$stmt->close();
$db->close();
?>