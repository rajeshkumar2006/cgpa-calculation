<?php
session_start();

// === Hardcoded admin credentials (change here if you want) ===
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'admin123';

// If already logged in, redirect to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = '⚠ Please enter both username and password.';
    } elseif ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = ADMIN_USER;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = '❌ Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin Login • CGPA Portal</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #4f46e5, #3b82f6);
    }
    .card {
      max-width: 420px;
      width: 100%;
      padding: 28px;
      border-radius: 16px;
      box-shadow: 0 12px 35px rgba(0,0,0,.15);
      animation: fadeIn 0.6s ease-in-out;
      background: #fff;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }
    .title {
      font-weight: bold;
      color: #1e3a8a;
    }
    .tagline {
      font-size: 0.9rem;
      color: #6b7280;
      margin-bottom: 20px;
    }
    .input-group-text {
      background: transparent;
      border-left: none;
      cursor: pointer;
    }
    .form-control:focus {
      box-shadow: none;
      border-color: #3b82f6;
    }
  </style>
</head>
<body>
  <div class="card">
    <h3 class="mb-1 text-center title">Admin Login</h3>
    <p class="tagline text-center">Secure access for administrators</p>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" placeholder="Enter admin username" required
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input type="password" id="pwd" name="password" class="form-control" placeholder="Enter password" required>
          <span class="input-group-text" onclick="togglePwd()">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </span>
        </div>
      </div>


      <div class="d-grid mb-2">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>

    <hr>
    <a href="index.php" class="btn btn-link w-100">← Back to Home</a>
  </div>

  <script>
    function togglePwd(){
      const p = document.getElementById('pwd');
      const eye = document.getElementById('eyeIcon');
      if (p.type === 'password') { 
        p.type = 'text'; 
        eye.classList.replace('bi-eye','bi-eye-slash');
      } else { 
        p.type = 'password'; 
        eye.classList.replace('bi-eye-slash','bi-eye');
      }
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
