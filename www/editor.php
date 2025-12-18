<?php
/** @var PDO $pdo */
session_start();
require_once("db_access.php");

// --------------------------------------------------
// Check whether user is logged in
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check Role: Editor Page only for Blogger an Admin

// session data comes from login.php / create_account.php
$roles = $_SESSION["user_roles"] ?? [];

if (
        // check whether current user is admin or blogger
        !in_array("admin", $roles) &&
        !in_array("blogger", $roles)
) {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- write title in Browser top -->
    <?php readfile(__DIR__ . "/assets/head.html"); ?>
    <title>Create new article</title>
</head>

<body>
<?php require_once("./assets/navbar.php"); ?>

<main class="container py-5">

    <h1>Create new article</h1>

    <!-- after sending form action = save_articles.php -->
    <form action="save_article.php" method="POST" enctype="multipart/form-data">

        <!-- -------------------------------------------------- -->
        <!-- TITLE -->
        <div class="mb-3">
            <!-- field heading -->
            <label for="title" class="form-label">Title</label>
            <!-- type = field type; id = label for="title" and JavaScript (document.getElementById); name = for PHP $_POST -->
            <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control"
                    required
            >
        </div>
        <!-- -------------------------------------------------- -->

        <!-- -------------------------------------------------- -->
        <!-- SUMMARY -->
        <div class="mb-3">
            <label for="summary" class="form-label">Summary</label>
            <textarea
                    id="summary"
                    class="form-control"
                    name="summary"
                    rows="2"
                    required
            ></textarea>
        </div>
        <!-- -------------------------------------------------- -->

        <!-- -------------------------------------------------- -->
        <!-- CONTENT -->
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea
                    id="content"
                    class="form-control"
                    name="content"
                    rows="10"
                    required
            ></textarea>
        </div>
        <!-- -------------------------------------------------- -->

        <!-- -------------------------------------------------- -->
        <!-- CATEGORIES -->
        <div class="mb-3">
            <label for="category" class="form-label">Categories</label>
            <select
                    id="category"
                    class="form-select"
                    name="category"
            >
                <?php
                // load categories
                $sql = "SELECT category_id, name FROM categories ORDER BY name";
                $stmt = $pdo->query($sql);

                // fetch, because we only return a single line at once; FETCH_ASSOC returns the key and value
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' .
                            htmlspecialchars($row['category_id'], ENT_QUOTES, 'UTF-8') . '">' .
                            htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') .
                            '</option>';
                }
                ?>
            </select>
        </div>
        <!-- -------------------------------------------------- -->

        <!-- -------------------------------------------------- -->
        <!-- Images -->
        <div class="mb-3">
            <label for="images" class="form-label">Images</label>

            <input
                    id="images"
                    class="form-control"
                    type="file"
                    name="images[]"
                    multiple
                    accept="image/jpeg,image/png,image/gif"
            >

            <small class="text-muted">
                Multiple pictures can be chosen.
            </small>
        </div>
        <!-- -------------------------------------------------- -->

        <!-- -------------------------------------------------- -->
        <!-- Submit Button -->
        <button class="btn btn-primary" type="submit">
            Publish
        </button>
        <!-- -------------------------------------------------- -->
    </form>

</main>

<?php readfile("./assets/footer.html"); ?>
</body>
</html>