<?php
function login(string $user_email, string $user_pw): void
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
    // Check input
    if ($user_email === "" || $user_pw === "") {
        $_SESSION["flash_error"] = "Email or password empty.";
        header("Location: /index.php");
        exit;
    }
    // --------------------------------------------------

    // --------------------------------------------------
    // DB connection
    $db = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

    if ($db->connect_error) {
        $_SESSION["flash_error"] = "Database connection failed.";
        header("Location: /index.php");
        exit;
    }
    // --------------------------------------------------

    // --------------------------------------------------
    // Load user
    $sql = "
        SELECT
            user_id,
            username,
            password_hash,
            role
        FROM users
        WHERE email = ?
          AND is_active = 1
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION["flash_error"] = "Invalid email or password.";
        header("Location: /index.php");
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();
    // --------------------------------------------------

    // --------------------------------------------------
    // Password check
    if (hash("sha256", $user_pw) !== $user["password_hash"]) {
        $_SESSION["flash_error"] = "Invalid email or password.";
        header("Location: /index.php");
        exit;
    }
    // --------------------------------------------------

    // --------------------------------------------------
    // Set session
    $_SESSION["user_id_logged_in"]   = (int)$user["user_id"];
    $_SESSION["user_name_logged_in"] = $user["username"];
    $_SESSION["user_role"]           = $user["role"]; // 'admin' or 'blogger'
    // --------------------------------------------------

    // --------------------------------------------------
    // Login log
    $sql = "
        INSERT INTO login_logs (
            FK_user_id,
            ip_address,
            device_info,
            success
        ) VALUES (?, ?, ?, 1)
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param(
        "iss",
        $user["user_id"],
        $_SERVER["REMOTE_ADDR"],
        $_SERVER["HTTP_USER_AGENT"]
    );
    $stmt->execute();
    $stmt->close();
    // --------------------------------------------------

    $db->close();

    header("Location: /index.php");
    exit;
}