<?php
require 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$post_id = (int)$_POST['post_id'];
$comment = trim($_POST['comment']);
$user_id = $_SESSION['user_id'];

if ($comment !== '') {
  $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
  $stmt->execute([$post_id, $user_id, $comment]);
}

header("Location: index.php");
exit;
?>
