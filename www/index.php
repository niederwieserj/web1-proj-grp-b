<?php
session_start();

require_once("db_access.php");
require_once("login.php");
require_once("logout.php");
require_once("create_account.php");

if (isset($_POST["action"]))
{
  switch ($_POST["action"])
  {
    case "login":
      if (isset($_POST["user-mail"], $_POST["user-pw"])) {
        login($_POST["user-mail"], $_POST["user-pw"]);
      }
      break;

    case "create-account":
      if (isset($_POST["username"], $_POST["user-mail"], $_POST["user-pw"])) {
        create_account($_POST["username"], $_POST["user-mail"], $_POST["user-pw"]);
      }
      break;

    case "logout":
      logout();
      break;
  }
}

$_SESSION["current_page"] = "Home";
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">

<head>
  <?php readfile("./assets/head.html"); ?>
  <link href="./assets/css/blog.css" rel="stylesheet" />
  
  <title>Leddit - Home</title>
</head>

<body>
    <?php
        require_once("./assets/navbar.php");

        if (!empty($_SESSION["flash_error"])): ?>
          <div class="toast-container position-fixed top-0 end-0 p-3">
              <div class="toast text-bg-danger show" role="alert">
                  <div class="toast-header">
                      <strong class="me-auto">Error</strong>
                      <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                  </div>
                  <div class="toast-body">
                      <?= htmlspecialchars($_SESSION["flash_error"]) ?>
                  </div>
              </div>
          </div>
        <?php unset($_SESSION["flash_error"]); endif;
    ?>

  <main class="container">
    <?php require_once("./display_content/main_posts.php");?>

    <div class="row mt-5 pe-0">
      <div class="col-md-8 pe-0">
        <h3 class="pb-4 mb-4 fst-italic border-bottom">Featured posts</h3>

        <?php require_once("./display_content/featured_posts.php");?>
      </div>
      <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem">
          <div class="p-4 mb-3 bg-body-tertiary rounded">
            <h4 class="fst-italic">About</h4>
            <p class="mb-0">
              Leddit lets you quickly create and share articles or blog posts on any topic. With a clean editor and smart discovery, you can reach readers who care about your ideas. Publish, connect, and inspire — all in one place.
            </p>
          </div>
          <?php require_once("./display_content/recent_posts.php"); ?>
        </div>
      </div>
    </div>
  </main>
  <?php readfile("./assets/footer.html"); ?>
</body>

</html>