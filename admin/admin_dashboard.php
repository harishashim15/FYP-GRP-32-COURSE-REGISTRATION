<?php
session_start();

// Simple guard (dummy auth for now)
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.html');
  exit();
}

$adminName = $_SESSION['name'] ?? 'Admin User';
$adminRole = $_SESSION['role'] ?? 'admin';

// Hardcoded dummy dashboard data (no DB)
$studentsRegistered = 128;
$advisorsRegistered  = 8;
$subjectsRegistered  = 36;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="template/assets/vendors/mdi/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="template/assets/vendors/flag-icon-css/css/flag-icon.min.css" />
    <link rel="stylesheet" href="template/assets/vendors/css/vendor.bundle.base.css" />
    <link rel="stylesheet" href="template/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css" />
    <link rel="stylesheet" href="template/assets/css/style.css" />
    <link rel="shortcut icon" href="template/assets/images/favicon.png" />
  </head>
  <body>
    <div class="container-scroller">
      <?php /* Reuse existing admin template (lightly customized inline) */ ?>
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <div class="text-center sidebar-brand-wrapper d-flex align-items-center">
          <a class="sidebar-brand brand-logo" href="admin_dashboard.php"><img src="template/assets/images/logo.svg" alt="logo" /></a>
          <a class="sidebar-brand brand-logo-mini pl-4 pt-3" href="admin_dashboard.php"><img src="template/assets/images/logo-mini.svg" alt="logo" /></a>
        </div>

        <ul class="nav">
          <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
              <div class="nav-profile-image">
                <img src="template/assets/images/faces/face1.jpg" alt="profile" />
                <span class="login-status online"></span>
              </div>
              <div class="nav-profile-text d-flex flex-column pr-3">
                <span class="font-weight-medium mb-2"><?php echo htmlspecialchars($adminName); ?></span>
                <span class="font-weight-normal"><?php echo htmlspecialchars($adminRole); ?></span>
              </div>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="admin_dashboard.php">
              <i class="mdi mdi-home menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="profile.php">
              <i class="mdi mdi-account-circle menu-icon"></i>
              <span class="menu-title">View Profile</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="edit_profile.php">
              <i class="mdi mdi-pencil menu-icon"></i>
              <span class="menu-title">Edit Profile</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="forgot_password.php">
              <i class="mdi mdi-lock-reset menu-icon"></i>
              <span class="menu-title">Forgot Password</span>
            </a>
          </li>

          <li class="nav-item sidebar-actions">
            <div class="nav-link">
              <div class="mt-4">
                <div class="border-none">
                  <p class="text-black">Account</p>
                </div>
                <ul class="mt-4 pl-0">
                  <li><a href="../logout.php" class="text-dark">Sign Out</a></li>
                </ul>
              </div>
            </div>
          </li>
        </ul>
      </nav>

      <div class="container-fluid page-body-wrapper">
        <nav class="navbar col-lg-12 col-12 p-lg-0 fixed-top d-flex flex-row">
          <div class="navbar-menu-wrapper d-flex align-items-stretch justify-content-between">
            <button class="navbar-toggler navbar-toggler align-self-center mr-2" type="button" data-toggle="minimize">
              <i class="mdi mdi-menu"></i>
            </button>
            <ul class="navbar-nav">
              <li class="nav-item nav-search border-0 ml-1 ml-md-3 ml-lg-5 d-none d-md-flex">
                <form class="nav-link form-inline mt-2 mt-md-0">
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" />
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                    </div>
                  </div>
                </form>
              </li>
            </ul>

            <ul class="navbar-nav navbar-nav-right ml-lg-auto">
              <li class="nav-item nav-profile dropdown border-0">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-toggle="dropdown">
                  <img class="nav-profile-img mr-2" alt="" src="template/assets/images/faces/face1.jpg" />
                  <span class="profile-name"><?php echo htmlspecialchars($adminName); ?></span>
                </a>
                <div class="dropdown-menu navbar-dropdown w-100" aria-labelledby="profileDropdown">
                  <a class="dropdown-item" href="profile.php"><i class="mdi mdi-account-circle mr-2 text-success"></i> Profile</a>
                  <a class="dropdown-item" href="../logout.php"><i class="mdi mdi-logout mr-2 text-primary"></i> Signout</a>
                </div>
              </li>
            </ul>

            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
              <span class="mdi mdi-menu"></span>
            </button>
          </div>
        </nav>

        <div class="main-panel">
          <div class="content-wrapper pb-0">
            <div class="page-header flex-wrap">
              <h3 class="mb-0">Admin Dashboard</h3>
              <div class="d-flex">
                <a class="btn btn-sm bg-white btn-icon-text border" href="profile.php">
                  <i class="mdi mdi-account btn-icon-prepend"></i> Profile
                </a>
                <a class="btn btn-sm bg-white btn-icon-text border ml-3" href="edit_profile.php">
                  <i class="mdi mdi-pencil btn-icon-prepend"></i> Edit
                </a>
              </div>
            </div>

            <div class="row">
              <div class="col-xl-3 col-lg-12 stretch-card grid-margin">
                <div class="card bg-warning">
                  <div class="card-body px-3 py-4">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="color-card">
                        <p class="mb-0 color-card-head">Students Registered</p>
                        <h2 class="text-white"><?php echo (int)$studentsRegistered; ?></h2>
                      </div>
                      <i class="card-icon-indicator mdi mdi-account-multiple bg-inverse-icon-warning"></i>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-xl-3 col-lg-12 stretch-card grid-margin">
                <div class="card bg-danger">
                  <div class="card-body px-3 py-4">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="color-card">
                        <p class="mb-0 color-card-head">Academic Advisors</p>
                        <h2 class="text-white"><?php echo (int)$advisorsRegistered; ?></h2>
                      </div>
                      <i class="card-icon-indicator mdi mdi-account-circle bg-inverse-icon-danger"></i>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-xl-3 col-lg-12 stretch-card grid-margin">
                <div class="card bg-primary">
                  <div class="card-body px-3 py-4">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="color-card">
                        <p class="mb-0 color-card-head">Subjects Registered</p>
                        <h2 class="text-white"><?php echo (int)$subjectsRegistered; ?></h2>
                      </div>
                      <i class="card-icon-indicator mdi mdi-book-open bg-inverse-icon-primary"></i>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-xl-3 col-lg-12 stretch-card grid-margin">
                <div class="card bg-success">
                  <div class="card-body px-3 py-4">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="color-card">
                        <p class="mb-0 color-card-head">Quick Actions</p>
                        <h2 class="text-white">Go</h2>
                      </div>
                      <i class="card-icon-indicator mdi mdi-lightning-bolt bg-inverse-icon-success"></i>
                    </div>
                    <div class="mt-3">
                      <a class="btn btn-light btn-sm" href="forgot_password.php">Forgot Password</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-xl-12 stretch-card grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Dashboard Overview</h4>
                    <p class="text-muted">Dummy data for now (replace with real DB queries later).</p>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="border rounded p-3">
                          <h5 class="mb-1">Students Registered</h5>
                          <div class="display-6"><?php echo (int)$studentsRegistered; ?></div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="border rounded p-3">
                          <h5 class="mb-1">Academic Advisors</h5>
                          <div class="display-6"><?php echo (int)$advisorsRegistered; ?></div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="border rounded p-3">
                          <h5 class="mb-1">Subjects Registered</h5>
                          <div class="display-6"><?php echo (int)$subjectsRegistered; ?></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © bootstrapdash.com 2020</span>
              <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center"> Free <a href="https://www.bootstrapdash.com/" target="_blank">Bootstrap dashboard template</a> from Bootstrapdash.com</span>
            </div>
          </footer>
        </div>
      </div>

    <!-- plugins:js -->
    <script src="template/assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="template/assets/vendors/chart.js/Chart.min.js"></script>
    <script src="template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="template/assets/vendors/flot/jquery.flot.js"></script>
    <script src="template/assets/vendors/flot/jquery.flot.resize.js"></script>
    <script src="template/assets/vendors/flot/jquery.flot.categories.js"></script>
    <script src="template/assets/vendors/flot/jquery.flot.fillbetween.js"></script>
    <script src="template/assets/vendors/flot/jquery.flot.stack.js"></script>
    <script src="template/assets/vendors/flot/jquery.flot.pie.js"></script>

    <script src="template/assets/js/off-canvas.js"></script>
    <script src="template/assets/js/hoverable-collapse.js"></script>
    <script src="template/assets/js/misc.js"></script>
    <script src="template/assets/js/dashboard.js"></script>
  </body>
</html>

