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

    // ===============================
    // LOAD USER DATA + CORRESPONDING ROLE
    // ===============================
    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.password_hash,
            r.role_name
        FROM users u
        LEFT JOIN user_roles ur ON ur.FK_user_id = u.user_id
        LEFT JOIN roles r ON r.role_id = ur.FK_role_id
        WHERE u.email = ?
    ";

    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Login failed";
        exit();
    }

    $roles = [];
    $user = null;

    while ($row = $result->fetch_assoc()) {
        if ($user === null) {
            $user = $row;
        }

        if ($row["role_name"]) {
            $roles[] = $row["role_name"];
        }
    }

    $stmt->close();


    // ===============================
    // Check if credentials are valid
    // ===============================
    $pw_sha256 = hash("sha256", $user_pw);

    if ($pw_sha256 !== $user["password_hash"]) {
        echo "Login failed";
        exit();
    }

    // ===============================
    // Set Session Values
    // ===============================
    $_SESSION["user_id_logged_in"]   = $user["user_id"];
    $_SESSION["user_name_logged_in"] = $user["username"];
    $_SESSION["user_roles"]          = $roles; // ARRAY!

    // ===============================
    // Insert Login Log
    // ===============================
    $success = 1;

    $sql = "INSERT INTO login_logs (FK_user_id, ip_address, device_info, success)
            VALUES (?, ?, ?, ?)";

    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param(
        "issi",
        $user["user_id"],
        $_SERVER["REMOTE_ADDR"],
        $_SERVER["HTTP_USER_AGENT"],
        $success
    );
    $stmt->execute();
    $stmt->close();

    $db_obj->close();
}
?>