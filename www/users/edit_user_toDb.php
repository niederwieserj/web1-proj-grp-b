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
$post_user_id = (int)($_POST['user_id'] ?? 0);

if ($post_user_id <= 0) {
    die("Invalid user.");
}
// --------------------------------------------------

// --------------------------------------------------
// Permission check (admin OR owner)
$currentUserId = (int)$_SESSION["user_id_logged_in"];
$currentRole   = $_SESSION["user_role"] ?? null;

$isOwner = ($post_user_id == $currentUserId);
$isAdmin = ($currentRole === "admin");

if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    die("You are not allowed to edit this user.");
}
// --------------------------------------------------

// --------------------------------------------------
// Read remaining POST data
$post_email = trim($_POST['email'] ?? '');
$post_username = trim($_POST['username'] ?? '');

if (empty($post_email) || empty($post_username)) {
    die("Invalid input.");
}

// --------------------------------------------------

// --------------------------------------------------
// Update user
$sql = "
    UPDATE users
    SET username = ?, email = ?
    WHERE user_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$post_username, $post_email, $post_user_id]);
// --------------------------------------------------

// --------------------------------------------------
// Redirect
header("Location: user_profile.php?id=". $post_user_id);
exit;
// --------------------------------------------------