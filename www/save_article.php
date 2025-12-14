<?php
session_start();
require_once("db_access.php");

/* ============================================================
   AUTHORIZATION
   ============================================================ */
if (!array_intersect(["blogger", "admin"], $_SESSION["user_roles"] ?? [])) {
    http_response_code(403);
    die("Not allowed");
}

$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    die("Not logged in");
}

/* ============================================================
   SLUG FUNCTIONS
   ============================================================ */
function slugify(string $str): string {
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $str), '-'));
}

function make_unique_slug(PDO $pdo, string $slug): string {
    $base = $slug;
    $i = 1;

    while (true) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() == 0) {
            return $slug;
        }
        $slug = $base . "-" . $i++;
    }
}

/* ============================================================
   INPUT
   ============================================================ */
$title   = trim($_POST["title"] ?? "");
$summary = trim($_POST["summary"] ?? "");
$content = trim($_POST["content"] ?? "");
$cats    = $_POST["categories"] ?? [];

if ($title === "" || $content === "") {
    die("Title and content required");
}

/* ============================================================
   INSERT ARTICLE
   ============================================================ */
$slug = make_unique_slug($pdo, slugify($title));

$stmt = $pdo->prepare("
    INSERT INTO articles (FK_user_id, title, slug, summary, content)
    VALUES (:uid, :title, :slug, :summary, :content)
");
$stmt->execute([
    ":uid"     => $user_id,
    ":title"   => $title,
    ":slug"    => $slug,
    ":summary" => $summary,
    ":content" => $content
]);

$article_id = $pdo->lastInsertId();

/* ============================================================
   CATEGORIES
   ============================================================ */
if (!empty($cats)) {
    $stmt = $pdo->prepare("
        INSERT INTO article_categories (FK_article_id, FK_category_id)
        VALUES (:aid, :cid)
    ");
    foreach ($cats as $cid) {
        $stmt->execute([
            ":aid" => $article_id,
            ":cid" => (int)$cid
        ]);
    }
}

/* ============================================================
   IMAGE UPLOAD (MAX 10 MB)
   ============================================================ */
$upload_dir = "picture-uploads/articles/";
$allowed = ["image/jpeg", "image/png", "image/gif"];
$max_size = 10 * 1024 * 1024;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (!empty($_FILES["images"]["name"])) {
    $stmt = $pdo->prepare("
        INSERT INTO article_images (FK_article_id, file_path, alt_text)
        VALUES (:aid, :path, '')
    ");

    foreach ($_FILES["images"]["name"] as $i => $name) {

        if ($_FILES["images"]["error"][$i] !== UPLOAD_ERR_OK) continue;
        if ($_FILES["images"]["size"][$i] > $max_size) {
            die("Image exceeds 10 MB limit");
        }
        if (!in_array($_FILES["images"]["type"][$i], $allowed)) {
            die("Invalid image type");
        }

        $safe_name = uniqid("img_", true) . "_" . basename($name);
        $path = $upload_dir . $safe_name;

        move_uploaded_file($_FILES["images"]["tmp_name"][$i], $path);

        $stmt->execute([
            ":aid"  => $article_id,
            ":path" => $path
        ]);
    }
}

/* ============================================================
   REDIRECT
   ============================================================ */
header("Location: article.php?slug=" . urlencode($slug));
exit;
