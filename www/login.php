<?php
function login($username, $password):void
{
    require_once("db_access.php");

    if (empty($username) || empty($password)) {
        echo "Username or password empty";
        exit();
    }

    $db_obj = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

    if ($db_obj->connect_error) {
        echo "Connection Error: " . $db_obj->connect_error;
        exit();
    }

    // Check if credentials are valid
    $pw_sha256 = hash('sha256', $password);

    $sql = "SELECT `user_id`, `password_hash` FROM `users` WHERE `email` = ?";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $stmt->bind_result($user_id, $pw_sha256_db);
    $stmt->fetch();
    echo $user_id;
    $stmt->close();

    $success = 0;

    if ($pw_sha256 === $pw_sha256_db) {
        // echo "Login successful!";
        $success = 1;
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