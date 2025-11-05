<?php
require 'includes/db.php';
require 'includes/header.php';

// Redirect if not logged in
if (!is_logged_in()) {
  header("Location: login.php");
  exit;
}

// Get current user's info
$user_id = $_SESSION['user_id'];
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

// Fetch only this user's posts (latest first)
$stmtPosts = $pdo->prepare("
  SELECT * FROM posts 
  WHERE user_id = ? 
  ORDER BY created_at DESC
");
$stmtPosts->execute([$user_id]);
$posts = $stmtPosts->fetchAll();
?>

<h2>My Profile</h2>
<div style="display:flex;align-items:center;gap:15px;margin-bottom:20px;">
  <img src="<?php echo htmlspecialchars($user['profile_photo'] ?? 'uploads/default.png'); ?>" 
       style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
  <div>
    <strong style="font-size:20px;"><?php echo htmlspecialchars($user['name']); ?></strong><br>
    <span style="color:gray;"><?php echo htmlspecialchars($user['email']); ?></span><br>
    <small>Joined on <?php echo date('d M Y', strtotime($user['created_at'])); ?></small>
  </div>
</div>

<hr>
<h3>Your Posts</h3>

<?php if (count($posts) == 0): ?>
  <p>You haven’t posted anything yet.</p>
<?php else: ?>
  <?php foreach ($posts as $post): ?>
    <div class="post">
      <div class="meta" style="color:gray;font-size:12px;">
        <?php echo $post['created_at']; ?>
      </div>
      <div style="margin-top:6px;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
      <?php if ($post['image']): ?>
        <img src="<?php echo htmlspecialchars($post['image']); ?>" style="max-width:100%;margin-top:8px;border-radius:8px;">
      <?php endif; ?>
      <br>
      <a href="edit_post.php?id=<?php echo $post['id']; ?>">Edit</a> |
      <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Delete this post?')">Delete</a>
    </div>
    <hr>
  <?php endforeach; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
