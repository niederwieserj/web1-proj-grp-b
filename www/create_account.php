<?php
function create_account( $user_username, $user_mail, $user_pw) {
    require_once("db_access.php");

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

    $sql = "SELECT `user_id` FROM `users` WHERE `email` = ?";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("s", $user_mail);
    $stmt->execute();

    $stmt->bind_result($user_id);
    $stmt->fetch();

    $_SESSION["user_id_logged_in"] = $user_id;
    $_SESSION["user_name_logged_in"] = $user_username;
    $_SESSION["user_role"] = "blogger"; // TODO: Load from DB

    $db_obj->close(); // Close must always be the last thing before exiting
}
?>
