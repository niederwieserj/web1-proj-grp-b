<div class="container">
    <header class="border-bottom lh-1 py-3">
        <div class="row flex-nowrap justify-content-between align-items-center">
            <div class="col-4 d-flex justify-content-end">

            </div>
            <div class="col-4 text-center">
                <a class="blog-header-logo text-body-emphasis text-decoration-none" href="/index.php">
                    Leddit
                </a>
            </div>
            <div class="col-4 d-flex justify-content-end align-items-center">
                <a class="link-secondary" href="#" aria-label="Search"
                   data-bs-toggle="modal" data-bs-target="#searchModal">
                    <svg class="bi mx-3" aria-hidden="true" width="20" height="20">
                        <use xlink:href="/assets/bootstrap-5.3.8/bootstrap-icons-1.13.1/bootstrap-icons.svg#search"></use>
                    </svg>
                </a>
                <?php if (isset($_SESSION["user_name_logged_in"])): ?>
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <?php echo $_SESSION["user_name_logged_in"]; ?>
                  </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#">Profile</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">My articles</a>
                        </li>

                        <!-- ADMIN ONLY -->
                        <?php if (in_array("admin", $_SESSION["user_roles"] ?? [])): ?>
                            <li>
                                <a class="dropdown-item" href="/admin.php">
                                    Admin Panel
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item" href="#">Settings</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <form method="post" action="index.php" class="inline m-0">
                                <button type="submit" name="action" value="logout" class="dropdown-item">
                                    Log out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <?php else: ?>
                  <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#loginModal" href="#">
                      Log in
                  </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <div class="nav-scroller py-1 mb-3 border-bottom">
        <nav class="nav nav-underline justify-content-between">
            <a class="nav-item nav-link link-body-emphasis active" href="#">Home</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Lifestyle</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Travel</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Food</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Technology</a>
            <a class="nav-item nav-link link-body-emphasis" href="#">Health</a>
        </nav>
    </div>


<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="loginModalLabel">Log in</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/index.php" method="POST">
              <div class="form-floating mb-3">
                <input
                  type="email"
                  class="form-control rounded-3"
                  id="user-mail"
                  name="user-mail"
                  placeholder="name@example.com"
                  required
                />
                <label for="user-mail">Email address</label>
              </div>
              <div class="form-floating mb-3">
                <input
                  type="password"
                  class="form-control rounded-3"
                  id="user-pw"
                  name="user-pw"
                  placeholder="user-pw"
                  required
                />
                <label for="floatingPassword">Password</label>
              </div>
              <button
                class="w-100 mb-2 btn btn-lg rounded-3 btn-primary"
                type="submit"
                required
              >
                Log in
              </button>
              <small class="text-body-secondary"
                >Don't have an account yet? Click <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#signupModal">here</a> to sign up.</small
              >
              <input type="hidden" name="action" value="login" />
            </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="signupModalLabel">Sign up</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="/index.php" method="POST">
              <div class="form-floating mb-3">
                <input
                  type="text"
                  class="form-control rounded-3"
                  id="username"
                  name="username"
                  placeholder="LedditLionheart"
                  required
                />
                <label for="username">Username</label>
              </div>
              <div class="form-floating mb-3">
                <input
                  type="email"
                  class="form-control rounded-3"
                  id="user-mail"
                  name="user-mail"
                  placeholder="name@example.com"
                  required
                />
                <label for="user-mail">Email address</label>
              </div>
              <div class="form-floating mb-3">
                <input
                  type="password"
                  class="form-control rounded-3"
                  id="user-pw"
                  name="user-pw"
                  placeholder="user-pw"
                  required
                />
                <label for="floatingPassword">Password</label>
              </div>
              <button
                class="w-100 mb-2 btn btn-lg rounded-3 btn-primary"
                type="submit"
              >
                Sign up
              </button>
              <small class="text-body-secondary"
                >By clicking sign up, you agree to our terms of service.</small
              >
              <input type="hidden" name="action" value="create-account" />
            </form>
      </div>
    </div>
  </div>
</div>

    <!-- ========================================================= -->
    <!-- SEARCH MODAL (Bootstrap)                                  -->
    <!-- ========================================================= -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h1 class="modal-title fs-5">Search articles</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- INPUT -->
                    <input
                            type="text"
                            id="searchBox"
                            class="form-control form-control-lg mb-3"
                            placeholder="Type to search..."
                            autocomplete="off"
                    >

                    <!-- RESULTS -->
                    <div id="searchResults" class="list-group"></div>

                </div>

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

        let html = "";
        for (const a of data) {
            html += `
            <a href="/article.php?slug=${a.slug}" class="list-group-item list-group-item-action">
                <strong>${a.title}</strong><br>
                <small class="text-muted">${a.summary ?? ''}</small>
            </a>
        `;
        }

        results.innerHTML = html;
    });
</script>