<?php
    $query = "SELECT article_id, title, articles.created_at, file_path FROM articles INNER JOIN article_images ON article_id=FK_article_id ORDER BY created_at DESC LIMIT 3;";
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
            <img src="./articles/<?php echo $art_img ?>" width="120" height="100" style="object-fit: cover;">
            <div class="col-lg-8">
                <h6 class="mb-0">
            <?php echo $art_title; ?>
            </h6>
                <small class="text-body-secondary">
            <?php echo $art_time; ?>
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
