<?php
function create_account($user_username, $user_mail, $user_pw): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once("db_access.php");

    $db_host = "database";
    $db_user = "root";
    $db_password = "tiger";
    $db_database = "blog";
    $db_port = 3306;

    // --------------------------------------------------
    if (empty($user_mail) || empty($user_pw) || empty($user_username)) {
        $_SESSION["flash_error"] = "Username, email or password empty";
        header("Location: /index.php");
        exit;
    }

    // Trim input
    $user_username = trim($user_username);
    $user_mail     = trim($user_mail);
    // --------------------------------------------------

    // --------------------------------------------------
    $db_obj = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

    if ($db_obj->connect_error) {
        $_SESSION["flash_error"] = "Database connection failed.";
        header("Location: /index.php");
        exit;
    }
    // --------------------------------------------------

    // --------------------------------------------------
    $pw_sha256 = hash('sha256', $user_pw);
    // --------------------------------------------------

    // --------------------------------------------------
    // role is automatically 'blogger' (DEFAULT in DB)
    $sql = "
        INSERT INTO users (username, password_hash, email)
        VALUES (?, ?, ?)
    ";

    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("sss", $user_username, $pw_sha256, $user_mail);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        $_SESSION["flash_error"] = "Username or email already exists.";
        $stmt->close();
        header("Location: /index.php");
        exit;
    }
    // --------------------------------------------------

    // --------------------------------------------------
    $user_id = $db_obj->insert_id;

    $stmt->close();

    if (!$user_id) {
        $_SESSION["flash_error"] = "Account creation failed.";
        header("Location: /index.php");
        exit;
    }
    // --------------------------------------------------

    // --------------------------------------------------
    // set session
    $_SESSION["user_id_logged_in"]   = $user_id;
    $_SESSION["user_name_logged_in"] = $user_username;
    $_SESSION["user_role"]           = "blogger";
    // --------------------------------------------------

    $db_obj->close();

    header("Location: /index.php");
    exit;
}