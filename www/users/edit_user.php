<?php
/** @var PDO $pdo */
session_start();
$_SESSION["current_page"] = "";

require_once("../db_access.php");
require_once("../display_content/format_date.php");

// $_GET["id"] comes from URL
$user_id = (int) ($_GET["id"] ?? 0);

// --------------------------------------------------
// Check whether user ID is given
if (!$user_id) {
    http_response_code(401);
    die("User not found.");
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

<body style="min-height: 100vh; display: flex; flex-direction: column;">
    <?php require_once("../assets/navbar.php"); ?>

    <main class="container py-5" style="flex: 1;">

        <form method="post" action="edit_user_toDb.php">
            <div class="row g-4">
                <?php if (!empty($user["username"])) { ?>

                    <!-- Profilbild -->
                    <div class="col-md-4 text-center">
                        <svg class="bi mx-1 mb-4" aria-hidden="true" width="64" height="64">
                            <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#person-circle">
                            </use>
                        </svg>

                        <form method="post" action="save_profile_picture.php" enctype="multipart/form-data">
                            <div class="mb-2">
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm mt-2">
                                Upload new image
                            </button>
                        </form>
                    </div>

                    <!-- Userdaten -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Profile Information</h5>

                                <input type="hidden" name="user_id" id="user_id" value="<?= $user_id ?>">

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($user["username"]) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user["email"]) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Member since</label>

                                    <input type="text" class="form-control" value="<?php echo format_date(htmlspecialchars($user["created_at"])) ?>" readonly disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($user_id === $_SESSION["user_id_logged_in"] || $user_role === "admin") { ?>
                        <div class="d-flex gap-2 flex-row-reverse mt-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="user_profile.php?id=<?php echo $user_id ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    <?php } ?>


                <?php } else {
                    echo "User not found.";
                }
                ?>
            </div>
        </form>

    </main>

    <?php readfile("../assets/footer.html"); ?>
</body>

</html>