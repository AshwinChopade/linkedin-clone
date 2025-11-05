<?php
require 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user_id'];

// Check if user already liked
$stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
if ($stmt->fetch()) {
  // Unlike
  $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?")->execute([$post_id, $user_id]);
} else {
  // Like
  $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)")->execute([$post_id, $user_id]);
}

header("Location: index.php");
exit;
?>
