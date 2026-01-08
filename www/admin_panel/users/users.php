<?php
/** @var PDO $pdo */
session_start();
require_once("../../db_access.php");

// --------------------------------------------------
// Check whether user is logged in
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check role (admin only)

// session data comes from login.php / create_account.php
$user_role = $_SESSION["user_role"] ?? null;

if ($user_role !== "admin") {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load All Users (title, timestamp) from users
$sql = "SELECT user_id, username, email, created_at, updated_at
        FROM users
        WHERE is_active = 1";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely
$stmt->execute();
// get result; fetchAll, because we return all lines; FETCH_ASSOC returns the key and value
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- write title in Browser top -->
    <?php readfile("../../assets/head.html"); ?>
    <title>Users</title>
</head>

<body>
<?php require_once("../../assets/navbar.php"); ?>

<main class="container py-5">

    <h1>Users</h1>


    <!-- STRUCTURE
        $users = [
            [
                "user_id" => "...",
                "email" => "...",
                "created_at" => "...",
                "updated_at" => "..."
            ]
        ]
    -->

    <table class="table align-middle">
        <thead>

        <!--
            <tr>
                <?php foreach (array_keys($users[0]) as $column): ?>
                    <th><?= htmlspecialchars($column) ?></th>
                <?php endforeach; ?>
            </tr>
            -->

        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Created</th>
            <th>Updated</th>
            <th>Deactivate</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($users as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row["username"]) ?></td>
                <td><?= htmlspecialchars($row["email"]) ?></td>
                <td><?php echo format_date(htmlspecialchars($row["created_at"]))?></td>
                <td><?php echo format_date(htmlspecialchars($row["updated_at"]))?></td>

                <td>
                    <?php if ($row["user_id"] != $user_id): ?>
                        <form method="post" action="delete_user.php" onsubmit="return confirm('Deactivate User?');">

                            <!-- send value user_id via POST to delete_user -->
                            <input type="hidden" name="target_user_id" value="<?= (int)$row["user_id"] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                Deactivate
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">You</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</main>

<?php readfile("../../assets/footer.html"); ?>
</body>
</html>