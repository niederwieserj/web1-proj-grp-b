<?php
/** @var PDO $pdo */
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
// Check uploaded file
if (
    !isset($_FILES["profile_image"]) ||
    $_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK
) {
    header("Location: user_profile.php?id=" . (int)$user_id . "&page=1");
    exit;
}

$file = $_FILES["profile_image"];
// --------------------------------------------------

// --------------------------------------------------
// Transaction as if something goes wrong, a roll back is executed
$pdo->beginTransaction();

try {

    // --------------------------------------------------
    // Validate image
    if ($file["size"] > 2 * 1024 * 1024) {
        throw new Exception("Image too large (max 2MB)");
    }

    $mime = mime_content_type($file["tmp_name"]);
    if (!in_array($mime, ["image/jpeg", "image/png", "image/webp"])) {
        throw new Exception("Invalid image type");
    }

    // --------------------------------------------------
    // Create upload directory if not exists
    //  -> $uploadDirUrl: URL path (what goes into <img src> and DB)
    //  -> $uploadDirDisk: filesystem path (for move_uploaded_file / unlink)
    $uploadDirUrl  = "/users/picture-uploads/users/";
    $uploadDirDisk = $_SERVER["DOCUMENT_ROOT"] . $uploadDirUrl;

    if (!is_dir($uploadDirDisk)) {
        mkdir($uploadDirDisk, 0777, true);
    }

    // --------------------------------------------------
    // Delete old profile image if exists
    $stmtOld = $pdo->prepare("
        SELECT file_path
        FROM user_image
        WHERE FK_user_id = ?
    ");
    $stmtOld->execute([$user_id]);
    $old_path = $stmtOld->fetchColumn();

    if ($old_path) {
        // old_path contains the URL path we stored in DB (e.g. "/users/picture-uploads/users/user_1_....png")
        $oldDiskPath = $_SERVER["DOCUMENT_ROOT"] . $old_path;
        if (is_file($oldDiskPath)) {
            unlink($oldDiskPath);
        }
    }

    // --------------------------------------------------
    // Generate filename
    $extension = match ($mime) {
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
    };

    $filename = "user_" . $user_id . "_" . time() . "." . $extension;

    // full filesystem path
    $diskPath = $uploadDirDisk . $filename;
    // URL path to store in DB and use in <img src="">
    $urlPath  = $uploadDirUrl . $filename;

    // --------------------------------------------------
    // Move uploaded file
    if (!move_uploaded_file($file["tmp_name"], $diskPath)) {
        throw new Exception("Failed to save image");
    }

    // --------------------------------------------------
    // Insert / update image in DB
    $stmtImage = $pdo->prepare("
        INSERT INTO user_image (FK_user_id, file_path, alt_text)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            file_path = VALUES(file_path),
            alt_text  = VALUES(alt_text)
    ");
    $stmtImage->execute([
        $user_id,
        $urlPath, // store URL path, not filesystem path
        'Profile image of user ' . $user_id
    ]);

    // --------------------------------------------------
    // Commit transaction
    $pdo->commit();

} catch (Throwable $e) {
    $pdo->rollBack();
    die($e->getMessage());
    die("Error saving profile image");
}

// --------------------------------------------------
// Redirect back to profile page
header("Location: user_profile.php?id=" . (int)$user_id . "&page=1");
exit;