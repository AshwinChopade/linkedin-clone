<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() { return isset($_SESSION['user_id']); }
function current_user_name() { return $_SESSION['user_name'] ?? ''; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LinkedIn Clone</title>
<style>
body {
  margin: 0;
  font-family: 'Inter', sans-serif;
  background: linear-gradient(180deg, #0a66c2 0%, #004182 100%);
  color: #fff;
  text-align: center;
}

.header {
  padding-top: 30px;
  padding-bottom: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.header h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  letter-spacing: 0.5px;
}

.header-nav {
  margin-top: 12px;
}

.header-nav a {
  display: inline-block;
  background: #ffffff;
  color: #0a66c2;
  padding: 8px 18px;
  border-radius: 25px;
  margin: 5px;
  font-weight: 600;
  text-decoration: none;
  transition: 0.3s;
}

.header-nav a:hover {
  background: #e8f3ff;
  transform: translateY(-2px);
}

.nav-user {
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.nav-user img {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  border: 2px solid white;
  object-fit: cover;
}

.nav-user span {
  color: white;
  font-weight: 500;
}
</style>
</head>
<body>

<div class="header">
  <h1>LinkedIn Clone</h1>

  <div class="header-nav">
    <?php if (is_logged_in()): ?>
      <div class="nav-user">
        <img src="<?php echo htmlspecialchars($_SESSION['user_photo'] ?? 'uploads/default.png'); ?>" alt="Profile">
        <span><?php echo htmlspecialchars(current_user_name()); ?></span>
        <a href="logout.php">Logout</a>
      </div>
    <?php else: ?>
      <a href="login.php">Login</a>
      <a href="register.php">Sign Up</a>
    <?php endif; ?>
  </div>
</div>
