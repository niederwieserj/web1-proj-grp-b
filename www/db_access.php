<?php
    // TODO: Hash and replace pw (find better solution than place it here)
    // TODO: Create read-only user for login
    // TODO: Create read-write user for sign-up
    // Credentials from file sample.env
    $db_host = "database";
    $db_user = "root";
    $db_password = "tiger";
    $db_database = "blog";
    $db_port = 3306;

    // Create PDO connection
    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_database;charset=utf8mb4";

        $pdo = new PDO($dsn, $db_user, $db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            "error" => "Database connection failed",
            "details" => $e->getMessage()
        ]);
        die();
    }
?>
