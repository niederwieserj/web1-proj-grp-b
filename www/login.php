<?php
function login($user_email, $user_pw):void
{
    require_once("db_access.php");

    if (empty($user_email) || empty($user_pw)) {
        echo "Username or password empty";
        exit();
    }

    $db_obj = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

    if ($db_obj->connect_error) {
        echo "Connection Error: " . $db_obj->connect_error;
        exit();
    }

    // Check if credentials are valid
    $pw_sha256 = hash('sha256', $user_pw);

    $sql = "SELECT `user_id`, `username`, `password_hash` FROM `users` WHERE `email` = ?";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();

    $stmt->bind_result($user_id, $user_username, $pw_sha256_db);
    $stmt->fetch();
    // echo $user_id;
    $stmt->close();

    $success = intval($pw_sha256 === $pw_sha256_db);

    if ($pw_sha256 === $pw_sha256_db) {
        echo "Login successful!";
        $_SESSION["user_id_logged_in"] = $user_id;
        $_SESSION["user_name_logged_in"] = $user_username;
        $_SESSION["user_role"] = "blogger"; // TODO: Load from DB
    } else {
        // echo "Login failed";
    }

    // echo "<p>" . $_SERVER['REMOTE_ADDR'] . "</p>";
    // echo "<p>" . $_SERVER['HTTP_USER_AGENT'] . "</p>";

    if ($user_id) {
        // If user exists, add login log
        $sql = "INSERT INTO `login_logs` (`FK_user_id`, `ip_address`, `device_info`, `success`) VALUES (?, ?, ?, ?)";
        $stmt = $db_obj->prepare($sql);
        $stmt->bind_param("issi", $user_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $success);
        $stmt->execute();
        $stmt->close();
    }

    $db_obj->close(); // Close must always be the last thing before exiting

    // Start session, set current user, redirect to homepage
}
?>