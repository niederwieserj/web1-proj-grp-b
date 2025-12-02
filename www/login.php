<?php
require_once("db_access.php");

$db_obj = new mysqli($host, $user, $password, $database, 3306);
if ($db_obj->connect_error) {
echo "Connection Error: " . $db_obj->connect_error;
exit();
}

/*
$sql = "SELECT * FROM users";
$stmt = $db_obj->prepare($sql);
$stmt->execute();
$stmt->bind_result($username, $user_id, $created_at, $is_active, $email, $password, $phone, $updated_at);
*/

/*$sql = "SELECT username FROM users";
$stmt = $db_obj->prepare($sql);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
echo $username;*/

// TODO: Hash pw
// TODO: Validate params

$username = $_POST["user-mail"];
$password = $_POST["user-pw"];

// Check if credentials are valid
$sql = "SELECT COUNT(*) FROM `users` WHERE `email` = ? AND `password_hash` = ?";
$stmt = $db_obj->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

$stmt->bind_result($count);
$stmt->fetch();

if ($count === 1) {
    echo "Login successful!";
}

echo $count;

// TODO: Add login log

?>
