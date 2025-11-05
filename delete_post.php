<?php
require 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
$stmt->execute([$id]);
$post = $stmt->fetch();
if ($post && $post['user_id'] == $_SESSION['user_id']) {
  if ($post['image'] && file_exists($post['image'])) unlink($post['image']);
  $pdo->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);
}
header("Location: index.php");
exit;
?>