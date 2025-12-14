<?php
session_start();
require_once("db_access.php");

/* ============================================================
   AUTH
   ============================================================ */
if (!array_intersect(["blogger", "admin"], $_SESSION["user_roles"] ?? [])) {
    http_response_code(403);
    die("Not allowed");
}

$user_id = $_SESSION["user_id_logged_in"] ?? null;
$slug = $_GET["slug"] ?? $_POST["slug"] ?? null;
if (!$user_id || !$slug) die("Invalid request");

/* ============================================================
   LOAD ARTICLE
   ============================================================ */
$stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ?");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) die("Article not found");

$is_admin = in_array("admin", $_SESSION["user_roles"]);
$is_owner = ($article["FK_user_id"] == $user_id);

if (!$is_admin && !$is_owner) {
    http_response_code(403);
    die("You may only edit your own articles");
}

/* ============================================================
   SLUG HELPERS
   ============================================================ */
function slugify(string $str): string {
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $str), '-'));
}

function make_unique_slug(PDO $pdo, string $slug, int $id): string {
    $base = $slug;
    $i = 1;

    while (true) {
        $stmt = $pdo->prepare("SELECT article_id FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        $found = $stmt->fetchColumn();

        if (!$found || $found == $id) return $slug;
        $slug = $base . "-" . $i++;
    }
}

/* ============================================================
   UPDATE
   ============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title   = trim($_POST["title"]);
    $summary = trim($_POST["summary"]);
    $content = trim($_POST["content"]);
    $cats    = $_POST["categories"] ?? [];

    if ($title === "" || $content === "") {
        die("Title and content required");
    }

    $new_slug = make_unique_slug(
        $pdo,
        slugify($title),
        $article["article_id"]
    );

    $stmt = $pdo->prepare("
        UPDATE articles
        SET title = :title,
            slug = :slug,
            summary = :summary,
            content = :content
        WHERE article_id = :id
    ");
    $stmt->execute([
        ":title"   => $title,
        ":slug"    => $new_slug,
        ":summary" => $summary,
        ":content" => $content,
        ":id"      => $article["article_id"]
    ]);

    /* ========================================================
       CATEGORIES
       ======================================================== */
    $pdo->prepare("DELETE FROM article_categories WHERE FK_article_id = ?")
        ->execute([$article["article_id"]]);

    if (!empty($cats)) {
        $stmt = $pdo->prepare("
            INSERT INTO article_categories (FK_article_id, FK_category_id)
            VALUES (:aid, :cid)
        ");
        foreach ($cats as $cid) {
            $stmt->execute([
                ":aid" => $article["article_id"],
                ":cid" => (int)$cid
            ]);
        }
    }

    /* ========================================================
       IMAGE UPLOAD
       ======================================================== */
    $upload_dir = "picture-uploads/articles/";
    $allowed = ["image/jpeg", "image/png", "image/gif"];
    $max_size = 10 * 1024 * 1024;

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (!empty($_FILES["images"]["name"])) {
        $stmt = $pdo->prepare("
            INSERT INTO article_images (FK_article_id, file_path, alt_text)
            VALUES (:aid, :path, '')
        ");

        foreach ($_FILES["images"]["name"] as $i => $name) {
            if ($_FILES["images"]["error"][$i] !== UPLOAD_ERR_OK) continue;
            if ($_FILES["images"]["size"][$i] > $max_size) die("Image too large");
            if (!in_array($_FILES["images"]["type"][$i], $allowed)) die("Invalid image type");

            $safe = uniqid("img_", true) . "_" . basename($name);
            $path = $upload_dir . $safe;

            move_uploaded_file($_FILES["images"]["tmp_name"][$i], $path);

            $stmt->execute([
                ":aid"  => $article["article_id"],
                ":path" => $path
            ]);
        }
    }

    header("Location: article.php?slug=" . urlencode($new_slug));
    exit;
}

/* ============================================================
   LOAD CATEGORIES FOR FORM
   ============================================================ */
$all_categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$selected = $pdo->prepare("
    SELECT FK_category_id FROM article_categories WHERE FK_article_id = ?
");
$selected->execute([$article["article_id"]]);
$selected_categories = $selected->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("./assets/head.html"); ?>
    <title>Edit Article</title>
</head>
<body>

<?php require "./assets/navbar.php"; ?>

<main class="container py-5">

    <h1>Edit Article</h1>

    <form method="POST" enctype="multipart/form-data">

        <!-- WICHTIG: slug für POST -->
        <input type="hidden" name="slug"
               value="<?= htmlspecialchars($article["slug"]) ?>">

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input class="form-control"
                   name="title"
                   value="<?= htmlspecialchars($article["title"]) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Summary</label>
            <textarea class="form-control"
                      name="summary"
                      rows="3"><?= htmlspecialchars($article["summary"]) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control"
                      name="content"
                      rows="10"
                      required><?= htmlspecialchars($article["content"]) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Categories</label>
            <select class="form-select" name="categories[]" multiple>
                <?php foreach ($all_categories as $c): ?>
                    <option value="<?= $c["category_id"] ?>"
                        <?= in_array($c["category_id"], $selected_categories) ? "selected" : "" ?>>
                        <?= htmlspecialchars($c["name"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Add images</label>
            <input class="form-control" type="file" name="images[]" multiple>
        </div>

        <button class="btn btn-primary" type="submit">
            Save changes
        </button>

    </form>

</main>

<?php readfile("./assets/footer.html"); ?>
</body>
</html>

