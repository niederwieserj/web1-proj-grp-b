<?php
require_once("db_access.php");

$user_username = $_POST["user-mail"];
$user_mail = $_POST["user-mail"];
$user_pw = $_POST["user-pw"];

if (empty($user_mail) || empty($user_pw) || empty($user_username)) {
    echo "Username or password empty";
    exit();
}

$db_obj = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

if ($db_obj->connect_error) {
    echo "Connection Error: " . $db_obj->connect_error;
    exit();
}

$pw_sha256 = hash('sha256', $user_pw);

// Add new account
$sql = "INSERT INTO `users` (`username`, `password_hash`, `email`) VALUES (?, ?, ?)";
$stmt = $db_obj->prepare($sql);
$stmt->bind_param("sss", $user_username, $pw_sha256, $user_mail);
$stmt->execute();
$stmt->close();

$db_obj->close(); // Close must always be the last thing before exiting

// Start session, set current user, redirect to homepage
?>
