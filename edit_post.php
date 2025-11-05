<?php
require 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post || $post['user_id'] != $_SESSION['user_id']) { header("Location: index.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content']);
  $pdo->prepare("UPDATE posts SET content=? WHERE id=?")->execute([$content,$id]);
  header("Location: index.php");
  exit;
}
require 'includes/header.php';
?>
<h2>Edit Post</h2>
<form method="post">
  <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea><br><br>
  <button type="submit">Save</button>
</form>
<?php require 'includes/footer.php'; ?>