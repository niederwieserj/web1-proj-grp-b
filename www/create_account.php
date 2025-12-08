<?php
function create_account( $user_username, $user_mail, $user_pw) {
    require_once("db_access.php");

    if (empty($user_mail) || empty($user_pw) || empty($user_username)) {
        echo "Username, email or password empty";
        exit();
    }

    $db_obj = new mysqli($db_host, $db_user, $db_password, $db_database, $db_port);

    if ($db_obj->connect_error) {
        echo "Connection Error: " . $db_obj->connect_error;
        exit();
    }

    $pw_sha256 = hash('sha256', $user_pw);

    // ===============================
    // Add new User to database
    // ===============================
    $sql = "INSERT INTO users (username, password_hash, email)
            VALUES (?, ?, ?)";

    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("sss", $user_username, $pw_sha256, $user_mail);
    $stmt->execute();
    $stmt->close();

    // ===============================
    // Get user_id for role assignment later below
    // ===============================
    $user_id = $db_obj->insert_id;

    if (!$user_id) {
        echo "User creation failed";
        exit();
    }

    // ===============================
    // set blogger Role automatically
    // ===============================
    $sql = "
        INSERT INTO user_roles (FK_user_id, FK_role_id)
        SELECT ?, role_id FROM roles WHERE role_name = 'blogger'
    ";

    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // ===============================
    // load user roles (for session)
    // ===============================
    $sql = "
        SELECT r.role_name
        FROM roles r
        JOIN user_roles ur ON ur.FK_role_id = r.role_id
        WHERE ur.FK_user_id = ?
    ";

    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row["role_name"];
    }

    $stmt->close();

    // ===============================
    // set session
    // ===============================
    $_SESSION["user_id_logged_in"] = $user_id;
    $_SESSION["user_name_logged_in"] = $user_username;
    $_SESSION["user_roles"] = $roles; // ARRAY!

    $db_obj->close();
}
?>