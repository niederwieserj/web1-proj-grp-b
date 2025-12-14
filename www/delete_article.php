<?php
session_start();
require_once("db_access.php");

/* ============================================================
   AUTH CHECK
   ============================================================ */
if (
    !isset($_SESSION["user_id_logged_in"], $_SESSION["user_roles"]) ||
    !array_intersect(["blogger", "admin"], $_SESSION["user_roles"])
) {
    http_response_code(403);
    die("Not allowed");
}

$user_id = $_SESSION["user_id_logged_in"];

/* ============================================================
   SLUG (GET or POST)
   ============================================================ */
$slug = $_GET["slug"] ?? $_POST["slug"] ?? null;
if (!$slug) {
    http_response_code(400);
    die("Missing slug");
}

/* ============================================================
   LOAD ARTICLE
   ============================================================ */
$stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ?");
$stmt->execute([$slug]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    http_response_code(404);
    die("Article not found");
}

/* ============================================================
   PERMISSION CHECK
   ============================================================ */
$is_admin = in_array("admin", $_SESSION["user_roles"]);
$is_owner = ($article["FK_user_id"] == $user_id);

if (!$is_admin && !$is_owner) {
    http_response_code(403);
    die("You may only delete your own articles");
}

/* ============================================================
   DELETE IMAGES FROM DISK
   ============================================================ */
$img_stmt = $pdo->prepare(
    "SELECT file_path FROM article_images WHERE FK_article_id = ?"
);
$img_stmt->execute([$article["article_id"]]);

$basePath = $_SERVER["DOCUMENT_ROOT"];

foreach ($img_stmt->fetchAll(PDO::FETCH_COLUMN) as $path) {
    $fullPath = $basePath . "/" . ltrim($path, "/");
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

/* ============================================================
   DELETE ARTICLE (CASCADE deletes images + categories)
   ============================================================ */
$del_stmt = $pdo->prepare(
    "DELETE FROM articles WHERE article_id = ?"
);
$del_stmt->execute([$article["article_id"]]);

/* ============================================================
   REDIRECT
   ============================================================ */
header("Location: /my_blogs.php?deleted=1");
exit;