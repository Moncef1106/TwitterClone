<?php
require 'db.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's posts
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($user['username']) ?></h1>
    </div>
    
    <div class="nav">
        <a href="index.php">Home</a> | <a href="post.php">Tweet</a> | <a href="logout.php">Logout</a>
    </div>

    <div id="posts">
        <?php if (empty($posts)): ?>
            <div class="post">
                <p>No tweets yet. <a href="post.php" style="color: #1DA1F2;">Post your first tweet!</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <p><?= htmlspecialchars($post['caption']) ?></p>
                    <?php if ($post['image_path']): ?>
                        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post image">
                    <?php endif; ?>
                    <p class="post-meta">Posted on: <?= date('M j, Y g:i A', strtotime($post['created_at'])) ?></p>

                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) as like_count FROM likes WHERE post_id = :post_id");
                    $stmt->bindParam(':post_id', $post['id']);
                    $stmt->execute();
                    $likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];
                    ?>
                    <p class="like-count">♥ <?= $likeCount ?> likes</p>

                    <div class="comments-section">
                        <?php
                        $stmt = $conn->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = :post_id ORDER BY created_at DESC");
                        $stmt->bindParam(':post_id', $post['id']);
                        $stmt->execute();
                        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <h3>Comments</h3>
                        <?php if (empty($comments)): ?>
                            <p class="post-meta">No comments yet.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($comments as $comment): ?>
                                    <li>
                                        <strong style="color: #1DA1F2;">@<?= htmlspecialchars($comment['username']) ?></strong>
                                        <span><?= htmlspecialchars($comment['comment']) ?></span>
                                        <small class="post-meta"><?= date('M j, Y', strtotime($comment['created_at'])) ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>