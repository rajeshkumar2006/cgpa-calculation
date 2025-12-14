<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student GPA Management System - Merit Arts and Science College</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    /* ================= BODY ================= */
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      font-family: 'Poppins', sans-serif;
      color: #ffffff;
      background: url('MM') no-repeat center center fixed;
      background-size: cover;
      filter: brightness(1.1) contrast(1.1);
      overflow: hidden;
      text-align: center;
      position: relative;
    }

    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0);
      z-index: 0;
    }

    /* ================= HEADER ================= */
    .header {
      position: relative;
      z-index: 2;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-top: 30px;
      flex-wrap: wrap;
    }
    .header img {
      width: 80px;
      border-radius: 50%;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
      margin-right: 15px;
      z-index: 2;
    }
    .header h1 {
      font-size: 2.5rem;
      font-weight: 900;
      letter-spacing: 1.5px;
      margin: 0;
      color: #ffffff; /* College name in white */
      text-shadow: 0 2px 8px rgba(0,0,0,0.6);
    }
    h2 {
      font-size: 1.7rem;
      font-weight: 700;
      margin: 8px 0 20px;
      color: #ffffff; /* Project name in white */
      text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    p.lead {
      font-size: 1.1rem;
      letter-spacing: 0.5px;
      margin-bottom: 25px;
      color: #ffffff;
      text-shadow: 0 1px 4px rgba(0,0,0,0.5);
    }

    /* ================= GLASS CARD WITH BLUR ================= */
    .card {
      padding: 30px 25px;
      border-radius: 20px;
      background: rgba(255,255,255,0.12); /* Semi-transparent */
      backdrop-filter: blur(15px); /* Glass blur effect */
      border: 1px solid rgba(255,255,255,0.2);
      box-shadow: 0 8px 30px rgba(0,0,0,0.5);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      max-width: 350px;
      position: relative;
      z-index: 2;
      text-align: center;
      margin-left: auto;
      margin-right: 5%;
    }

    .card h3 {
      color: #FFD700; /* Welcome text in yellow */
      font-weight: bold;
      margin-bottom: 25px;
      text-shadow: 0 1px 5px rgba(0,0,0,0.5);
    }

    /* ================= BUTTONS ================= */
    .btn-big {
      padding: 12px 18px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 25px;
      transition: all 0.3s ease;
    }
    .btn-primary {
      background: linear-gradient(90deg, #0072ff, #00c6ff);
      border: none;
      box-shadow: 0 5px 15px rgba(0,114,255,0.6);
      color: #fff;
    }
    .btn-primary:hover {
      transform: scale(1.05);
      box-shadow: 0 0 25px rgba(0,198,255,0.9);
    }
    .btn-dark {
      background: linear-gradient(90deg, #0056b3, #0072ff);
      color: #fff;
      border: none;
      box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    }
    .btn-dark:hover {
      transform: scale(1.05);
      box-shadow: 0 0 25px rgba(0,114,255,0.5);
    }

    /* ================= FOOTER ================= */
    footer {
      margin-top: 35px;
      text-align: center;
      color: #ffffff;
      font-size: 14px;
      opacity: 0.9;
      position: relative;
      z-index: 2;
      margin-bottom: 20px;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 768px) {
      .card { margin-right: auto; margin-left: auto; }
      .header img { margin-right: 10px; margin-bottom: 10px; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <img src="logo.jpg" alt="College Logo">
    <h1>Merit Arts and Science College</h1>
  </div>

  <h2>Student GPA Management System</h2>
  <p class="lead">Excellence in Education • Empowering Future Leaders</p>

  <!-- Login Card -->
  <div class="card">
    <h3 class="mb-4 fw-bold"><i class="fa-solid fa-graduation-cap"></i> Welcome to CGPA Portal</h3>

    <div class="d-grid gap-3 mt-3">
      <a class="btn btn-primary btn-big" href="login_user.php">
        <i class="fa-solid fa-user-graduate"></i> Student Login
      </a>
      <a class="btn btn-dark btn-big" href="login_admin.php">
        <i class="fa-solid fa-user-shield"></i> Admin Login
      </a>
    </div>
  </div>

  <footer>
    © 2025 Merit Arts and Science College | Designed with ❤️
  </footer>

</body>
</html>
