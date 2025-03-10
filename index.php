<?php
require 'db.php';
session_start();

// Fetch posts with like and comment counts
$stmt = $conn->prepare("
    SELECT posts.*, users.username, 
           (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count,
           (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Z</title>
    <link rel="stylesheet" href="index.css">
    <style>
        
    </style>
</head>
<body>
    <div class="header">
        <h1>Twitter</h1>
        <div class="nav">
            <a href="index.php">Home</a> | <a href="post.php">Tweet</a> | <a href="logout.php">Logout</a>
        </div>
    </div>

    <div id="feed">
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-header">
                    <strong><?= htmlspecialchars($post['username']) ?></strong>
                </div>
                
                <div class="post-content">
                    <?= htmlspecialchars($post['caption']) ?>
                    <?php if ($post['image_path']): ?>
                        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image">
                    <?php endif; ?>
                </div>

                <p class="post-meta">Posted on: <?= date('M j, Y g:i A', strtotime($post['created_at'])) ?></p>
                
                <button class="like-btn" data-post-id="<?= $post['id'] ?>">
                    ♥ <span class="like-count"><?= $post['like_count'] ?></span>
                </button>

                <div class="comments">
                    <form class="comment-form" data-post-id="<?= $post['id'] ?>" onsubmit="handleCommentSubmit(event, this)">
                        <input type="text" name="comment" placeholder="Write a comment..." required>
                        <button type="submit">Reply</button>
                    </form>
                    <p>💬 <span class="comment-count"><?= $post['comment_count'] ?></span> replies</p>
                    <div class="comment-list" id="comments-<?= $post['id'] ?>">
                        <?php
                        $stmt = $conn->prepare("
                            SELECT comments.comment, users.username, comments.created_at 
                            FROM comments 
                            JOIN users ON comments.user_id = users.id 
                            WHERE comments.post_id = :post_id 
                            ORDER BY comments.created_at ASC
                        ");
                        $stmt->bindParam(':post_id', $post['id']);
                        $stmt->execute();
                        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($comments as $comment): ?>
                            <div class="comment">
                                <strong><?= htmlspecialchars($comment['username']) ?></strong>
                                <?= htmlspecialchars($comment['comment']) ?>
                                <small class="post-meta"><?= date('M j, Y', strtotime($comment['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    function handleCommentSubmit(event, form) {
        event.preventDefault();
        const postId = form.dataset.postId;
        const commentInput = form.querySelector('input[name="comment"]');
        const commentText = commentInput.value;
        
        fetch('get_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${postId}&comment=${encodeURIComponent(commentText)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update comment count
                const commentCountSpan = form.parentElement.querySelector('.comment-count');
                commentCountSpan.textContent = parseInt(commentCountSpan.textContent) + 1;
                
                // Fetch updated comments
                fetch(`get_comments.php?post_id=${postId}`)
                    .then(response => response.json())
                    .then(comments => {
                        const commentList = document.getElementById(`comments-${postId}`);
                        commentList.innerHTML = '';
                        
                        comments.forEach(comment => {
                            const commentDiv = document.createElement('div');
                            commentDiv.className = 'comment';
                            commentDiv.innerHTML = `
                                <strong>${comment.username}</strong>
                                ${comment.comment}
                                <small class="post-meta">${comment.created_at}</small>
                            `;
                            commentList.appendChild(commentDiv);
                        });
                    })
                    .catch(error => console.error('Error fetching comments:', error));
                
                // Clear input
                commentInput.value = '';
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
    <script src="js/script.js"></script>
</body>
</html>
