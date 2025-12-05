<?php
/** @var PDO $pdo */
session_start();
require_once("db_access.php");

// Check whether Upload-Folder exists
if (!is_dir("picture-uploads/articles")) {
    mkdir("picture-uploads/articles", 0777, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("./assets/head.html"); ?>
</head>

<body>
<?php require_once("./assets/navbar.php"); ?>

<main class="container py-5">
    <h1>Create new article</h1>

    <form action="save_article.php" method="POST" enctype="multipart/form-data">

        <!-- TITLE -->
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" required>
        </div>

        <!-- SUMMARY -->
        <div class="mb-3">
            <label class="form-label">Summary</label>
            <textarea class="form-control" name="summary" rows="2"></textarea>
        </div>

        <!-- CONTENT -->
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control" name="content" rows="10" required></textarea>
        </div>

        <!-- CATEGORIES -->
        <div class="mb-3">
            <label class="form-label">Categories</label>
            <select class="form-select" name="categories[]" multiple>
                <?php
                // Kategorien laden
                $sql = "SELECT category_id, name FROM categories ORDER BY name";
                $stmt = $pdo->query($sql);

                foreach ($stmt as $row) {
                    echo "<option value='{$row['category_id']}'>{$row['name']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- IMAGES -->
        <div class="mb-3">
            <label class="form-label">Images</label>
            <input class="form-control" type="file" name="images[]" multiple>
        </div>

        <button class="btn btn-primary" type="submit">
            Publish
        </button>
    </form>

</main>

<?php readfile("./assets/footer.html"); ?>
</body>
</html>