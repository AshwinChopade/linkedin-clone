<?php
require 'includes/db.php';
require 'includes/header.php';

// Handle registration logic
$message = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Handle photo upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    $photo = "uploads/default.png";
    if (!empty($_FILES["profile_photo"]["name"])) {
        $target_file = $target_dir . time() . "_" . basename($_FILES["profile_photo"]["name"]);
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $photo = $target_file;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, profile_photo) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $email, $password, $photo]);
        $message = "✅ Registration successful! You can now log in.";
    } catch (Exception $e) {
        $message = "⚠️ Error: Email already registered.";
    }
}
?>

<style>
body {
  margin: 0;
  font-family: 'Inter', sans-serif;
  background: linear-gradient(180deg, #0a66c2 0%, #004182 100%);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.form-container {
  background: #ffffff;
  width: 380px;
  padding: 35px 40px;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
  text-align: left;
  animation: fadeIn 0.8s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

h2 {
  text-align: center;
  color: #0a66c2;
  margin-bottom: 25px;
}

label {
  font-weight: 600;
  display: block;
  margin-top: 12px;
  color: #333;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"] {
  width: 100%;
  padding: 10px;
  margin-top: 6px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
}

button {
  width: 100%;
  background: #0a66c2;
  color: white;
  border: none;
  padding: 12px;
  margin-top: 25px;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}

button:hover {
  background: #004182;
}

.message {
  text-align: center;
  margin-bottom: 10px;
  font-weight: 500;
}

/* ✨ Footer Fix Starts Here */
footer {
  background: #ffffff;
  color: #0a66c2;
  text-align: center;
  border-radius: 10px;
  padding: 12px 20px;
  margin-top: 40px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.15);
  font-size: 13px;
  font-weight: 500;
  width: fit-content;
  margin-left: auto;
  margin-right: auto;
  animation: fadeIn 1s ease;
}

footer p {
  margin: 4px 0;
}
/* ✨ Footer Fix Ends Here */
</style>


<div class="form-container">
  <h2>Create Your Account</h2>
  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Profile Photo</label>
    <input type="file" name="profile_photo">

    <button type="submit">Register</button>
  </form>
</div>

<footer>
  <p>© <?php echo date('Y'); ?> LinkedIn Clone by <strong>Ashwin Chopade</strong></p>
</footer>
