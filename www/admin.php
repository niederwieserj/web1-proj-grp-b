<?php
    /** @var PDO $pdo */
    session_start();
    require_once("db_access.php");

    // ===============================
    // Check whether user is admin
    // ===============================
    if (
        !isset($_SESSION["user_roles"]) ||
        !in_array("admin", $_SESSION["user_roles"])
    ) {
        http_response_code(403);
        die("Admin access only");
    }

    // ===============================
    // load users + roles
    // ===============================
    $sql = "
        SELECT 
            u.user_id,
            u.username,
            u.email,
            GROUP_CONCAT(r.role_name SEPARATOR ', ') AS roles
        FROM users u
        LEFT JOIN user_roles ur ON ur.FK_user_id = u.user_id
        LEFT JOIN roles r ON r.role_id = ur.FK_role_id
        GROUP BY u.user_id
    ";

    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("./assets/head.html"); ?>
    <title>Admin Panel</title>
</head>
<body>

<?php require_once("./assets/navbar.php"); ?>

<main class="container py-5">
    <h1>User Management</h1>

    <table class="table table-bordered mt-4">
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Change Role</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= (int)$user["user_id"] ?></td>
                <td><?= htmlspecialchars($user["username"]) ?></td>
                <td><?= htmlspecialchars($user["email"]) ?></td>
                <td><?= htmlspecialchars($user["roles"]) ?></td>

                <!-- ROLE CHANGE -->
                <td>
                    <form method="post" action="change_role.php" class="d-flex gap-2">
                        <input type="hidden" name="user_id" value="<?= $user["user_id"] ?>">
                        <select name="role" class="form-select">
                            <option value="user">user</option>
                            <option value="blogger">blogger</option>
                            <option value="admin">admin</option>
                        </select>
                        <button class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>

                <!-- DELETE -->
                <td>
                    <form method="post" action="delete_user.php" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="user_id" value="<?= $user["user_id"] ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</main>

<?php readfile("./assets/footer.html"); ?>
</body>
</html>