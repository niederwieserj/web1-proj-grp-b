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
    <div class="row g-4">
        <?php if (!empty($user["username"])): ?>

            <!-- LEFT: Profile picture (own form only for the image upload) -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Profile picture</h5>

                        <?php if (!empty($user["file_path"])): ?>
                            <img
                                    src="<?= htmlspecialchars($user['file_path']) ?>"
                                    alt="<?= htmlspecialchars($user['alt_text'] ?? 'Profile image') ?>"
                                    class="rounded-circle mb-3"
                                    width="120" height="120"
                                    style="object-fit: cover;"
                            >
                        <?php else: ?>
                            <svg class="bi mb-3" aria-hidden="true" width="64" height="64">
                                <use xlink:href="./../assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#person-circle"></use>
                            </svg>
                        <?php endif; ?>

                        <!-- Separate form for uploading a new profile picture -->
                        <form method="post"
                              action="save_profile_picture.php"
                              enctype="multipart/form-data">
                            <div class="mb-2 text-start">
                                <label class="form-label mb-1">Choose new image</label>
                                <input type="file"
                                       name="profile_image"
                                       class="form-control"
                                       accept="image/*">
                            </div>

                            <button type="submit" class="btn btn-outline-primary btn-sm mt-2 w-100">
                                Upload &amp; save picture
                            </button>

                            <small class="text-muted d-block mt-2">
                                JPEG, PNG or WEBP, max. 2&nbsp;MB.
                                The picture is saved immediately after upload.
                            </small>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RIGHT: User data (separate form for username/email) -->
            <div class="col-md-8">
                <!-- Form for updating username and email only -->
                <form method="post" action="edit_user_toDb.php">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Profile information</h5>

                            <input type="hidden" name="user_id"
                                   value="<?= (int)$user_id ?>">

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text"
                                       name="username"
                                       class="form-control"
                                       value="<?= htmlspecialchars($user["username"]) ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       value="<?= htmlspecialchars($user["email"]) ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Member since</label>
                                <input type="text"
                                       class="form-control"
                                       value="<?= format_date($user["created_at"]) ?>"
                                       readonly disabled>
                            </div>

                            <?php if ($user_id === ($_SESSION["user_id_logged_in"] ?? null) || $user_role === "admin"): ?>
                                <div class="d-flex gap-2 justify-content-end mt-3">
                                    <a href="user_profile.php?id=<?= (int)$user_id ?>"
                                       class="btn btn-secondary">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Save changes
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-danger mb-0">
                    User not found.
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php readfile("../assets/footer.html"); ?>
</body>


</html>