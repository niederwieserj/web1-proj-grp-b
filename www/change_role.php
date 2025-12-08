<?php
/** @var PDO $pdo */
session_start();
require_once("db_access.php");

if (!in_array("admin", $_SESSION["user_roles"])) {
    die("Admin only");
}

$user_id = $_POST["user_id"] ?? null;
$role    = $_POST["role"] ?? null;

if (!$user_id || !$role) {
    die("Invalid input");
}

// delete old role
$stmt = $pdo->prepare("DELETE FROM user_roles WHERE FK_user_id = ?");
$stmt->execute([$user_id]);

// assign new role
$stmt = $pdo->prepare("
    INSERT INTO user_roles (FK_user_id, FK_role_id)
    SELECT ?, role_id FROM roles WHERE role_name = ?
");
$stmt->execute([$user_id, $role]);

header("Location: admin.php");
exit;