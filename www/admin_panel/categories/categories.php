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
// Load All Categories
$sqlAllCategories = "SELECT category_id, name, description
        FROM categories
        ";

// prepare SQL
$stmt = $pdo->prepare($sqlAllCategories);
// bind values safely
$stmt->execute();
// get result; fetchAll, because we return all lines; FETCH_ASSOC returns the key and value
$allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- write title in Browser top -->
    <?php readfile("../../assets/head.html"); ?>
    <title>Categories</title>
</head>

<body>
<?php require_once("../../assets/navbar.php"); ?>

<main class="container py-5">

    <div class="d-flex justify-content-between align-items-center">
        <h1>Categories</h1>

        <button type="button" class="btn btn-primary"
                data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            Add Category
        </button>
    </div>

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
                <?php foreach (array_keys($allCategories[0]) as $column): ?>
                    <th><?= htmlspecialchars($column) ?></th>
                <?php endforeach; ?>
            </tr>
            -->

            <tr>
                <th>Name</th>
                <th>Description</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($allCategories as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row["name"]) ?></td>
                <td><?= htmlspecialchars($row["description"]) ?></td>

                <td>
                    <form method="post" action="delete_categories.php" onsubmit="return confirm('Delete Category?');">

                        <!-- send value user_id via POST to delete_user -->
                        <input type="hidden" name="target_category_id" value="<?= (int)$row["category_id"] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                            Delete
                        </button>
                    </form>
                </td>

                <td>
                    <a href="edit_categories.php?id=<?= (int)$row['category_id'] ?>" class="btn btn-primary btn-sm">
                        Edit
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</main>

<!-- -------------------------------------------------- -->
<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="createCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- -------------------------------------------------- -->
            <form method="post" action="create_categories.php">
                <div class="modal-body">

                    <!-- -------------------------------------------------- -->
                    <!-- Category Name -->
                    <div class="mb-3">
                        <!-- field heading -->
                        <label for="name" class="form-label">Title</label>
                        <!-- type = field type; id = label for="title" and JavaScript (document.getElementById); name = for PHP $_POST -->
                        <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                required
                        >
                    </div>
                    <!-- -------------------------------------------------- -->

                    <!-- -------------------------------------------------- -->
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea
                                id="description"
                                class="form-control"
                                name="description"
                                rows="2"
                                required
                        ></textarea>
                    </div>
                    <!-- -------------------------------------------------- -->

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
            <!-- -------------------------------------------------- -->

        </div>
    </div>
</div>
<!-- -------------------------------------------------- -->

<?php readfile("../../assets/footer.html"); ?>
</body>
</html>