<?php
require 'includes/db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user_id'];
$view_profile = isset($_GET['profile']) ? intval($_GET['profile']) : 0;

/* -----------------------------------
   🗑️ DELETE POST HANDLER
----------------------------------- */
if (isset($_GET['delete'])) {
  $delete_id = intval($_GET['delete']);

  // Fetch post image for deletion
  $stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
  $stmt->execute([$delete_id, $user_id]);
  $post = $stmt->fetch();

  if ($post) {
    // Delete likes & comments
    $pdo->prepare("DELETE FROM likes WHERE post_id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?")->execute([$delete_id, $user_id]);

    // Remove image file
    if (!empty($post['image']) && file_exists($post['image'])) {
      unlink($post['image']);
    }
  }

  header('Location: index.php');
  exit();
}

/* -----------------------------------
   ❤️ LIKE POST HANDLER
----------------------------------- */
if (isset($_POST['like_post_id'])) {
  $post_id = intval($_POST['like_post_id']);
  $check = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
  $check->execute([$user_id, $post_id]);

  if ($check->rowCount() > 0) {
    $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?")->execute([$user_id, $post_id]);
  } else {
    $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)")->execute([$user_id, $post_id]);
  }

  header("Location: index.php");
  exit();
}

/* -----------------------------------
   💬 COMMENT HANDLER
----------------------------------- */
if (isset($_POST['comment_post_id'])) {
  $post_id = intval($_POST['comment_post_id']);
  $comment_text = trim($_POST['comment_text']);
  if (!empty($comment_text)) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $comment_text]);
  }
  header("Location: index.php");
  exit();
}

/* -----------------------------------
   📜 FETCH POSTS
----------------------------------- */
if ($view_profile) {
  $stmt = $pdo->prepare("
    SELECT p.*, u.name, u.profile_photo
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE u.id = ?
    ORDER BY p.created_at DESC
  ");
  $stmt->execute([$view_profile]);
} else {
  $stmt = $pdo->query("
    SELECT p.*, u.name, u.profile_photo
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
  ");
}
$posts = $stmt->fetchAll();

/* -----------------------------------
   📊 FETCH LIKES & COMMENTS
----------------------------------- */
$likes_stmt = $pdo->query("SELECT post_id, COUNT(*) as total FROM likes GROUP BY post_id");
$likes_data = [];
foreach ($likes_stmt as $row) {
  $likes_data[$row['post_id']] = $row['total'];
}

$user_likes = $pdo->prepare("SELECT post_id FROM likes WHERE user_id = ?");
$user_likes->execute([$user_id]);
$user_liked_posts = $user_likes->fetchAll(PDO::FETCH_COLUMN);

$comments_stmt = $pdo->query("
  SELECT c.*, u.name, u.profile_photo 
  FROM comments c 
  JOIN users u ON c.user_id = u.id 
  ORDER BY c.created_at ASC
");
$comments_data = [];
foreach ($comments_stmt as $c) {
  $comments_data[$c['post_id']][] = $c;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LinkedIn Clone - Feed</title>
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #f3f2ef;
    }

    /* --- Navbar --- */
    .navbar {
      background: #0a66c2;
      color: white;
      padding: 12px 40px;
      position: fixed;
      width: 100%;
      top: 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      z-index: 100;
      box-sizing: border-box;
    }

    .navbar-left {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .navbar-left h1 { margin: 0; font-size: 22px; }

    .home-btn {
      background: #fff;
      color: #0a66c2;
      padding: 6px 16px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
    }

    .home-btn:hover { background: #e8f3ff; }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-right: 30px; /* ✅ Added spacing from right edge */
      flex-shrink: 0;
    }

    .nav-right img {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 2px solid #fff;
      object-fit: cover;
    }

    .nav-right span {
      font-weight: 600;
      margin-left: 4px;
    }

    .logout-btn {
      background: #fff;
      color: #0a66c2;
      padding: 6px 14px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
      margin-left: 6px;
    }

    .logout-btn:hover { background: #e8f3ff; }

    /* --- Layout --- */
    .container {
      max-width: 750px;
      margin: 120px auto;
      padding: 0 20px;
    }

    .create-post, .post-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      margin-bottom: 30px;
    }

    .create-post {
      display: flex;
      gap: 15px;
    }

    .create-post img {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      border: 2px solid #0a66c2;
    }

    textarea {
      width: 100%;
      border-radius: 10px;
      border: 1px solid #ccc;
      padding: 10px;
      resize: none;
    }

    .post-btn {
      background: #0a66c2;
      color: white;
      padding: 8px 20px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 600;
    }

    .post-btn:hover {
      background: #004182;
    }

    .user-details {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-info img {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      border: 2px solid #0a66c2;
    }

    .delete-btn {
      background: #ff4d4d;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .delete-btn:hover {
      background: #d93636;
    }

    .post-content {
      margin-top: 10px;
      color: #333;
      line-height: 1.5;
    }

    .post-card img.post-image {
      width: 100%;
      border-radius: 10px;
      margin-top: 10px;
    }

    .post-actions {
      display: flex;
      justify-content: space-between;
      margin-top: 12px;
      padding-top: 8px;
      border-top: 1px solid #ddd;
    }

    .action-btn {
      background: #f3f2ef;
      border: none;
      border-radius: 20px;
      padding: 6px 16px;
      cursor: pointer;
      font-weight: 600;
      color: #0a66c2;
    }

    .action-btn:hover {
      background: #e8f3ff;
    }

    .comments {
      margin-top: 15px;
    }

    .comment {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      margin-bottom: 10px;
    }

    .comment img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid #0a66c2;
    }

    .comment-box {
      background: #f3f2ef;
      padding: 8px 12px;
      border-radius: 10px;
    }

    .comment-box strong {
      color: #0a66c2;
      font-size: 13px;
    }

    .comment-box p {
      margin: 4px 0 0;
      font-size: 14px;
    }

  </style>
</head>

<body>

  <!-- ✅ Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <h1>LinkedIn Clone</h1>
      <a href="index.php" class="home-btn">Home</a>
    </div>
    <div class="nav-right">
      <a href="index.php?profile=<?php echo $user_id; ?>">
        <img src="<?php echo htmlspecialchars($_SESSION['user_photo']); ?>" alt="Profile">
      </a>
      <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>

  <div class="container">
    <!-- ✅ Create Post -->
    <?php if (!$view_profile): ?>
      <div class="create-post">
        <img src="<?php echo htmlspecialchars($_SESSION['user_photo']); ?>" alt="Profile">
        <form method="POST" action="create_post.php" enctype="multipart/form-data" style="flex:1;">
          <textarea name="content" placeholder="What's on your mind, <?php echo htmlspecialchars($_SESSION['user_name']); ?>?" required></textarea>
          <input type="file" name="image" accept="image/*" style="margin-top:8px;">
          <button type="submit" class="post-btn" style="margin-top:8px;">Post</button>
        </form>
      </div>
    <?php endif; ?>

    <h2><?php echo $view_profile ? 'Your Posts' : 'Recent Posts'; ?></h2>

    <?php foreach ($posts as $post): ?>
      <div class="post-card">
        <div class="user-details">
          <div class="user-info">
            <img src="<?php echo htmlspecialchars($post['profile_photo']); ?>" alt="Profile">
            <div>
              <strong><?php echo htmlspecialchars($post['name']); ?></strong><br>
              <small><?php echo date('F j, Y • g:i A', strtotime($post['created_at'])); ?></small>
            </div>
          </div>

          <!-- ✅ Delete Button (only for owner) -->
          <?php if ($post['user_id'] == $user_id): ?>
            <a href="?delete=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">
              <button class="delete-btn">Delete</button>
            </a>
          <?php endif; ?>
        </div>

        <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        <?php if ($post['image']): ?>
          <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image">
        <?php endif; ?>

        <div class="post-actions">
          <form method="POST" style="display:inline;">
            <input type="hidden" name="like_post_id" value="<?php echo $post['id']; ?>">
            <button class="action-btn">
              👍 <?php echo in_array($post['id'], $user_liked_posts) ? 'Liked' : 'Like'; ?>
              (<?php echo $likes_data[$post['id']] ?? 0; ?>)
            </button>
          </form>
          <span>💬 <?php echo isset($comments_data[$post['id']]) ? count($comments_data[$post['id']]) : 0; ?> Comments</span>
        </div>

        <div class="comments">
          <?php if (!empty($comments_data[$post['id']])): ?>
            <?php foreach ($comments_data[$post['id']] as $comment): ?>
              <div class="comment">
                <img src="<?php echo htmlspecialchars($comment['profile_photo']); ?>" alt="Profile">
                <div class="comment-box">
                  <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                  <p><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <form method="POST" class="add-comment">
            <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
            <textarea name="comment_text" placeholder="Add a comment..." required></textarea>
            <button type="submit" class="post-btn" style="margin-top:6px;">Comment</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
