<?php
// session data comes from login.php / create_account.php

// save session role of user in $roles
$roles = $_SESSION["user_roles"] ?? [];
// get user id of user
$is_logged_in = isset($_SESSION["user_id_logged_in"]);
// assign roles (admin / blogger) based on $roles
$is_admin     = in_array("admin", $roles);
$is_blogger   = $is_admin || in_array("blogger", $roles);
?>

<div class="container">
    <header class="border-bottom lh-1 py-3">
        <div class="row flex-nowrap justify-content-between align-items-center">

            <!-- LEFT -->
            <!-- Create Article (only Blogger an Admin) -->
            <div class="col-4 d-flex justify-content-start align-items-center">
                <?php if ($is_blogger): ?>
                    <a href="/editor.php" class="btn btn-sm btn-success">
                        Create Article
                    </a>
                <?php endif; ?>
            </div>


            <!-- CENTER -->
            <div class="col-4 text-center">
                <a class="blog-header-logo text-body-emphasis text-decoration-none" href="/index.php">
                    Leddit
                </a>
            </div>

            <!-- RIGHT -->
            <div class="col-4 d-flex justify-content-end align-items-center">

                <!-- SEARCH -->
                <a class="link-secondary" href="#" aria-label="Search"
                   data-bs-toggle="modal" data-bs-target="#searchModal">
                    <svg class="bi mx-3" aria-hidden="true" width="20" height="20">
                        <use xlink:href="/assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#search"></use>
                    </svg>
                </a>

                <?php if ($is_logged_in): ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                type="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION["user_name_logged_in"]) ?>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item" href="#">
                                    Profile
                                </a>
                            </li>

                            <!-- BLOGGER + ADMIN -->
                            <?php if ($is_blogger): ?>
                                <li>
                                    <a class="dropdown-item" href="/my_articles.php">
                                        My Articles
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- ADMIN ONLY -->
                            <?php if ($is_admin): ?>
                                <li>
                                    <a class="dropdown-item" href="/all_articles.php">
                                        All Articles
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin_panel/admin_panel.php">
                                        Admin Panel
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <form method="post" action="/index.php" class="m-0">
                                    <button type="submit"
                                            name="action"
                                            value="logout"
                                            class="dropdown-item">
                                        Log out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-secondary"
                       data-bs-toggle="modal"
                       data-bs-target="#loginModal">
                        Log in
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- NAV CATEGORIES -->
    <div class="nav-scroller py-1 mb-3 border-bottom">
        <nav class="nav nav-underline justify-content-between">
            <a class="nav-item nav-link link-body-emphasis active" href="/index.php">Home</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Lifestyle</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Travel</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Food</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Technology</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Health</a>
        </nav>
    </div>
</div>

<!-- ========================================================= -->
<!-- LOGIN MODAL -->
<!-- ========================================================= -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h1 class="modal-title fs-5">Log in</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form action="/index.php" method="POST">

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="user-mail" required>
                        <label>Email address</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="user-pw" required>
                        <label>Password</label>
                    </div>

                    <input type="hidden" name="action" value="login">

                    <button class="w-100 btn btn-lg btn-primary">
                        Log in
                    </button>

                    <div class="text-center mt-3">
                        <small>
                            No account yet?
                            <a href="#"
                               data-bs-dismiss="modal"
                               data-bs-toggle="modal"
                               data-bs-target="#signupModal">
                                Sign up here
                            </a>
                        </small>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- SIGNUP MODAL                                              -->
<!-- ========================================================= -->
<div class="modal fade" id="signupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Sign up</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="/index.php" method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="username" required>
                        <label>Username</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="user-mail" required>
                        <label>Email address</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="user-pw" required>
                        <label>Password</label>
                    </div>
                    <button class="w-100 btn btn-lg btn-primary">Sign up</button>
                    <input type="hidden" name="action" value="create-account">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- SEARCH MODAL                                              -->
<!-- ========================================================= -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5">Search articles</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchBox"
                       class="form-control form-control-lg mb-3"
                       placeholder="Type to search..." autocomplete="off">
                <div id="searchResults" class="list-group"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("searchBox").addEventListener("input", async function () {
        const q = this.value.trim();
        const results = document.getElementById("searchResults");

        if (q.length < 2) {
            results.innerHTML = "";
            return;
        }

        const res = await fetch("/search_api.php?q=" + encodeURIComponent(q));
        const data = await res.json();

        results.innerHTML = data.map(a => `
        <a href="/article.php?id=${a.article_id}"
           class="list-group-item list-group-item-action">
            <strong>${a.title}</strong><br>
            <small class="text-muted">${a.summary ?? ""}</small>
        </a>
    `).join("");
    });
</script>
