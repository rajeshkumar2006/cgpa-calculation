<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Login • CGPA Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      font-family: "Segoe UI", sans-serif;
    }
    .card {
      max-width: 420px;
      width: 100%;
      padding: 30px;
      border-radius: 18px;
      background: #fff;
      box-shadow: 0 12px 40px rgba(0,0,0,.15);
      transition: all 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 50px rgba(0,0,0,.2);
    }
    h3 {
      font-weight: 700;
      color: #333;
    }
    .login-icon {
      font-size: 40px;
      color: #0d6efd;
      margin-bottom: 10px;
    }
    .form-control {
      border-radius: 10px;
      padding: 12px;
      border: 1px solid #ddd;
      transition: border-color 0.3s;
    }
    .form-control:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 5px rgba(13,110,253,.3);
    }
    .btn-primary {
      padding: 12px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      transform: scale(1.05);
    }
    .btn-link {
      color: #555;
      text-decoration: none;
      font-weight: 500;
    }
    .btn-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="card text-center">
    <div class="login-icon">🔑</div>
    <h3 class="mb-4">Student Login</h3>

    <?php if(isset($_SESSION['login_error'])): ?>
      <div class="alert alert-danger">
        <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
      </div>
    <?php endif; ?>

    <form method="post" action="student_auth.php">
      <div class="mb-3 text-start">
        <label class="form-label">Register Number</label>
        <input type="text" name="regno" class="form-control" placeholder="Enter your Register No." required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="dob" class="form-control" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>

    <hr>
    <a href="index.php" class="btn btn-link w-100">← Back to Home</a>
  </div>
</body>
</html>
