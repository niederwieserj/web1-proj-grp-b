<?php
/** @var PDO $pdo */
session_start();
require_once("../db_access.php");

// --------------------------------------------------
// Check login
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Read POST data
$article_id = (int)($_POST['article_id'] ?? 0);

if ($article_id <= 0) {
    die("Invalid article.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load owner for permission check
$stmt = $pdo->prepare("
    SELECT FK_user_id
    FROM articles
    WHERE article_id = ?
");
$stmt->execute([$article_id]);
$ownerId = $stmt->fetchColumn();

if (!$ownerId) {
    die("Article not found.");
}
// --------------------------------------------------

// --------------------------------------------------
// Permission check (admin OR owner)
$currentUserId = (int)$_SESSION["user_id_logged_in"];
$currentRole   = $_SESSION["user_role"] ?? null;

$isOwner = ($ownerId == $currentUserId);
$isAdmin = ($currentRole === "admin");

if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    die("You are not allowed to edit this article.");
}
// --------------------------------------------------

// --------------------------------------------------
// Read remaining POST data
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);

if ($title === '' || $content === '' || $category_id <= 0) {
    die("Invalid input.");
}

$return = $_POST['return'] ?? 'articles.php';
// --------------------------------------------------

// --------------------------------------------------
// Update article
$sql = "
    UPDATE articles
    SET title = ?, content = ?, FK_category_id = ?
    WHERE article_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$title, $content, $category_id, $article_id]);
// --------------------------------------------------


// Delete images:
if (!empty($_POST['delete_images'])) {

    // 1. Dateipfade holen
    $in  = implode(',', array_fill(0, count($_POST['delete_images']), '?'));
    $sql = "
        SELECT image_id, file_path
        FROM article_images
        WHERE image_id IN ($in)
          AND FK_article_id = ?
    ";

    $params = array_map('intval', $_POST['delete_images']);
    $params[] = $article_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Dateien löschen
    foreach ($files as $file) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $file['file_path'];
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    // 3. DB-Einträge löschen
    $sql = "
        DELETE FROM article_images
        WHERE image_id IN ($in)
          AND FK_article_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

// Add images:
// --------------------------------------------------
// Add images (FIXED VERSION – übernommen von save_article)
// --------------------------------------------------

$upload_dir = "picture-uploads/articles/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (!empty($_FILES["new_images"]["name"][0])) {

    $stmtImg = $pdo->prepare("
        INSERT INTO article_images (FK_article_id, file_path, alt_text)
        VALUES (?, ?, '')
    ");

    foreach ($_FILES["new_images"]["name"] as $i => $name) {

        if ($_FILES["new_images"]["error"][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        if ($_FILES["new_images"]["size"][$i] > 10 * 1024 * 1024) {
            continue;
        }

        $mime = mime_content_type($_FILES["new_images"]["tmp_name"][$i]);
        if (!in_array($mime, ["image/jpeg", "image/png", "image/gif"])) {
            continue;
        }

        $filename = uniqid("img_", true) . "_" . basename($name);
        $path = $upload_dir . $filename;

        move_uploaded_file($_FILES["new_images"]["tmp_name"][$i], $path);

        $stmtImg->execute([$article_id, $path]);
    }
}

// --------------------------------------------------

// --------------------------------------------------
// Redirect
header("Location: $return");
exit;
// --------------------------------------------------