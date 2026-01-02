<?php
session_start();
require_once("../db_access.php");

// --------------------------------------------------
// Check whether user is logged in
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check role (admin or blogger only)

// session data comes from login.php / create_account.php
$user_role = $_SESSION["user_role"] ?? null;

if (!in_array($user_role, ["admin", "blogger"], true)) {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Read input from editor.php
$title   = trim($_POST["title"] ?? "");
$content = trim($_POST["content"] ?? "");
$category = $_POST["category"] ?? null;

if ($title === "" || $content === "") {
    die("Title and content are required");
}

if (!$category) {
    die("Category required");
}

$category = (int)$category;
// --------------------------------------------------

// --------------------------------------------------
// Transaction as if something goes wrong, a roll back is executed
$pdo->beginTransaction();

try {
    // --------------------------------------------------
    // Insert article into DB
    $stmtArticle = $pdo->prepare("
        INSERT INTO articles (FK_user_id, FK_category_id, title, content)
        VALUES (?, ?, ?, ?)
    ");
    $stmtArticle->execute([
        $user_id,
        $category,
        $title,
        $content
    ]);

    // get article_id in order to assign categories to articles in the tavle article_cateogries in the next step
    $article_id = (int)$pdo->lastInsertId();
    // --------------------------------------------------

    // --------------------------------------------------
    // Images
    $upload_dir = "picture-uploads/articles/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_FILES["images"]["name"][0])) {
        $stmtImg = $pdo->prepare("
            INSERT INTO article_images (FK_article_id, file_path, alt_text)
            VALUES (?, ?, '')
        ");

        foreach ($_FILES["images"]["name"] as $i => $name) {
            if ($_FILES["images"]["error"][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($_FILES["images"]["size"][$i] > 10 * 1024 * 1024) {
                throw new Exception("Image too large");
            }

            $mime = mime_content_type($_FILES["images"]["tmp_name"][$i]);
            if (!in_array($mime, ["image/jpeg", "image/png", "image/gif"])) {
                throw new Exception("Invalid image type");
            }

            $filename = uniqid("img_", true) . "_" . basename($name);
            $path = $upload_dir . $filename;

            move_uploaded_file($_FILES["images"]["tmp_name"][$i], $path);

            $stmtImg->execute([$article_id, $path]);
        }
    }

    $pdo->commit();

} catch (Throwable $e) {
    $pdo->rollBack();
    die($e->getMessage());
    die("Error saving article");
}

// --------------------------------------------------
// Redirect to article by ID
header("Location: article.php?id=" . $article_id);
exit;