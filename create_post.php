<?php
require 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$content = trim($_POST['content']);
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
  $allowed = ['jpg','jpeg','png','gif'];
  $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
  if (in_array($ext, $allowed)) {
    if (!is_dir('uploads')) mkdir('uploads', 0755, true);
    $fileName = uniqid() . '.' . $ext;
    $target = 'uploads/' . $fileName;
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
    $imagePath = $target;
  }
}
$stmt = $pdo->prepare("INSERT INTO posts (user_id,content,image) VALUES (?,?,?)");
$stmt->execute([$_SESSION['user_id'], $content, $imagePath]);
header("Location: index.php");
exit;
?>