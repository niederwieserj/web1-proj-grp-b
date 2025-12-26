<?php
/** @var PDO $pdo */
session_start();
require_once("../../db_access.php");

// --------------------------------------------------
// Check login
$user_id = $_SESSION["user_id_logged_in"] ?? null;
if (!$user_id) {
    http_response_code(401);
    die("Not logged in.");
}
// --------------------------------------------------

// --------------------------------------------------
// Check role
$roles = $_SESSION["user_roles"] ?? [];
if (!in_array("admin", $roles)) {
    http_response_code(403);
    die("Access denied.");
}
// --------------------------------------------------

// --------------------------------------------------
// Load category
$category_id = (int)($_GET['id'] ?? 0);

$sql = "SELECT name, description
        FROM categories
        WHERE category_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Category not found.");
}
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php readfile("../../assets/head.html"); ?>
    <title>Edit Category</title>
</head>

<body>
<?php require_once("../../assets/navbar.php"); ?>

<main class="container py-5">

    <h1 class="mb-4">Edit Category</h1>

    <form method="post" action="editCategoriesToDB.php">

        <input type="hidden" name="category_id" value="<?= $category_id ?>">

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Title</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                value="<?= htmlspecialchars($category['name']) ?>"
                required
            >
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea
                id="description"
                name="description"
                class="form-control"
                rows="3"
                required><?= htmlspecialchars($category['description']) ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="categories.php" class="btn btn-secondary">Cancel</a>
        </div>

    </form>

</main>

<?php readfile("../../assets/footer.html"); ?>
</body>
</html>