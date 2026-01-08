<?php
/** @var PDO $pdo */
session_start();
require_once("../db_access.php");

// --------------------------------------------------
// Check whether user is logged in
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check role (admin or blogger only)

// session data comes from login.php / create_account.php
$user_role = $_SESSION["user_role"] ?? null;

if (!in_array($user_role, ["admin", "blogger"], true)) {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load User data from users
$sql = "SELECT users.user_id, users.username, users.email, users.created_at, user_image.file_path, user_image.alt_text
        FROM users
        LEFT JOIN user_image
            ON users.user_id = user_image.FK_user_id
        WHERE user_id = ?
        AND is_active = 1
        LIMIT 1;
";

// prepare SQL
$stmt = $pdo->prepare($sql);
// bind values safely
$stmt->execute([$user_id]);
// get result; fetch, because we return a single lines; FETCH_ASSOC returns the key and value
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// --------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- write title in Browser top -->
    <?php readfile("../assets/head.html"); ?>
    <title>My Articles</title>
</head>

<body>
<?php require_once("../assets/navbar.php"); ?>

<main class="container py-5">

    <div class="row g-4">
        <!-- Profilbild -->
        <div class="col-md-4 text-center">
            <img
                src="<?= $user["file_path"]
                    ? htmlspecialchars($user["file_path"])
                    : '/assets/img/default-avatar.png' ?>"
                class="img-fluid rounded-circle mb-3"
                alt="<?= htmlspecialchars($user["alt_text"] ?? 'Profile image') ?>"
                style="max-width: 200px;"
            >

            <form method="post" action="save_profile_picture.php" enctype="multipart/form-data">
                <div class="mb-2">
                    <input type="file" name="profile_image" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    Upload new image
                </button>
            </form>
        </div>

        <!-- Userdaten -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Profile Information</h5>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user["username"]) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user["email"]) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Member since</label>
                        
                        <input type="text" class="form-control" value="<?php echo format_date(htmlspecialchars($user["created_at"]))?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php readfile("../assets/footer.html"); ?>
</body>
</html>