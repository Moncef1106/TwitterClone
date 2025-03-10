<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    $stmt = $conn->prepare("
        SELECT comments.comment, users.username, comments.created_at 
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE comments.post_id = :post_id 
        ORDER BY comments.created_at ASC
    ");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comments);
} else {
    echo json_encode([]);
}
?>