<?php
    require_once("format_date.php");

    $query = "
    SELECT 
    a.article_id,
    a.title,
    a.created_at,
    ai.file_path
    FROM articles AS a
    JOIN article_images AS ai
    ON ai.FK_article_id = a.article_id
    AND ai.image_id = (
        SELECT MIN(image_id)
        FROM article_images
        WHERE FK_article_id = a.article_id
    )
    ORDER BY created_at DESC LIMIT 3;
    ";
    $db = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stmt->bind_result($art_id, $art_title, $art_time, $art_img);
?>

<div>
    <h4 class="fst-italic">Recent posts</h4>
    <ul class="list-unstyled">
        <?php while ($stmt->fetch()) { ?>
        <li>
            <a class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center py-3 link-body-emphasis text-decoration-none border-top"
            href="/articles/article.php?id=<?php echo $art_id; ?>">
            <img src="./articles/<?php echo $art_img ?>" width="120" height="100" style="object-fit: cover;" class="rounded">
            <div class="col-lg-8">
                <h6 class="mb-0">
            <?php echo $art_title; ?>
            </h6>
                <small class="text-body-secondary">
            <?php echo format_date($art_time); ?>
            </small>
            </div>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>

<?php
$stmt->close();
$db->close();
?>
