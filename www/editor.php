<?php
    /** @var PDO $pdo */
    session_start();
    require_once("db_access.php");


    // ===============================
    // ROLE CHECK: Editor Page only for Blogger an Admin
    // ===============================
    if (
            !isset($_SESSION["user_roles"]) ||
            !is_array($_SESSION["user_roles"]) ||
            !array_intersect(["blogger", "admin"], $_SESSION["user_roles"])
    ) {
        http_response_code(403);
        die("Access denied. You are not allowed to create articles.");
    }

    // ===============================
    // Check whether Upload-Folder exists
    // ===============================
    $uploadDir = "picture-uploads/articles";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("./assets/head.html"); ?>
    <title>Create new article</title>
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
                    $id = htmlspecialchars($row['category_id']);
                    $name = htmlspecialchars($row['name']);
                    echo "<option value='{$id}'>{$name}</option>";
                }
                ?>
            </select>
            <small class="text-muted">Halte STRG (Windows) oder CMD (Mac), um mehrere Kategorien zu wählen.</small>
        </div>

        <!-- IMAGES -->
        <div class="mb-3">
            <label class="form-label">Images</label>
            <input class="form-control" type="file" name="images[]" multiple>
            <small class="text-muted">Mehrere Bilder können ausgewählt werden.</small>
        </div>

        <button class="btn btn-primary" type="submit">
            Publish
        </button>
    </form>

</main>

<?php readfile("./assets/footer.html"); ?>
</body>
</html>