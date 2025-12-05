<?php
session_start();
require_once("db_access.php"); // stellt $pdo bereit

// ============================================================
// SLUG-function for URL
// ============================================================
function slugify($str) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str), '-'));
    return $slug;
}

function make_unique_slug(PDO $pdo, string $slug): string {
    $base = $slug;
    $i = 1;

    while (true) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        $count = (int)$stmt->fetchColumn();

        if ($count === 0) {
            return $slug;
        }

        $slug = $base . "-" . $i;
        $i++;
    }
}

// ============================================================
// Post Data
// ============================================================
$title    = $_POST["title"];
$summary  = $_POST["summary"];
$content  = $_POST["content"];
$user_id  = $_SESSION["user_id_logged_in"] ?? null;

if (!$user_id) {
    die("Not logged in");
}

// Create SLUG
$slug = slugify($title);
$slug = make_unique_slug($pdo, $slug);

// ============================================================
// Save Article
// ============================================================
$sql = "INSERT INTO articles (FK_user_id, title, slug, summary, content)
        VALUES (:uid, :title, :slug, :summary, :content)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":uid"     => $user_id,
    ":title"   => $title,
    ":slug"    => $slug,
    ":summary" => $summary,
    ":content" => $content
]);

$article_id = $pdo->lastInsertId();

// ============================================================
// Save Categories
// ============================================================
if (isset($_POST["categories"])) {
    $sql = "INSERT INTO article_categories (FK_article_id, FK_category_id)
            VALUES (:aid, :cid)";
    $stmt = $pdo->prepare($sql);

    foreach ($_POST["categories"] as $cat_id) {
        $stmt->execute([
            ":aid" => $article_id,
            ":cid" => $cat_id
        ]);
    }
}

// ============================================================
// Save Pictures
// ============================================================
$upload_dir = "picture-uploads/articles/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if (!empty($_FILES["images"]["tmp_name"])) {
    $sql = "INSERT INTO article_images (FK_article_id, file_path, alt_text)
            VALUES (:aid, :path, '')";
    $stmt = $pdo->prepare($sql);

    foreach ($_FILES["images"]["tmp_name"] as $i => $tmp) {
        if (!$tmp) continue;

        $filename = time() . "_" . basename($_FILES["images"]["name"][$i]);
        $path = $upload_dir . $filename;

        move_uploaded_file($tmp, $path);

        $stmt->execute([
            ":aid"  => $article_id,
            ":path" => $path
        ]);
    }
}

// ============================================================
// Article Redirect
// ============================================================
header("Location: article.php?slug=" . urlencode($slug));
exit();