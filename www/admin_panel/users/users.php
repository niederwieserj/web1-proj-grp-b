<?php
$_SESSION["current_page"] = "";

/** @var PDO $pdo */
session_start();
require_once("../../db_access.php");
require_once("../../display_content/format_date.php");

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

<body style="min-height: 100vh; display: flex; flex-direction: column;">
<?php require_once("../../assets/navbar.php"); ?>

<main class="container py-5" style="flex: 1;">

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

    <table class="table table-hover align-middle">
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
            <th>Edit</th>
            <th>Deactivate</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($users as $row): ?>
            <tr>
                <td>
                    <a href="/users/user_profile.php?id=<?= htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($row["username"]) ?>
                        </a>
                    
                </td>
                <td><?= htmlspecialchars($row["email"]) ?></td>
                <td><?php echo format_date(htmlspecialchars($row["created_at"]))?></td>
                <td><?php echo format_date(htmlspecialchars($row["updated_at"]))?></td>

                <td>
                    <a href="../../users/edit_user.php?id=<?= (int)$row['user_id'] ?>" class="btn btn-outline-primary btn-sm">
                        <svg class="bi mx-1" aria-hidden="true" width="16" height="16">
                            <use xlink:href="../../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#pencil">
                            </use>
                        </svg>
                    </a>
                </td>

                <td>
                    <?php if ($row["user_id"] != $user_id): ?>
                        <form method="post" action="delete_user.php" onsubmit="return confirm('Deactivate User?');">

                            <!-- send value user_id via POST to delete_user -->
                            <input type="hidden" name="target_user_id" value="<?= (int)$row["user_id"] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
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