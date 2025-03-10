<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    $post_id = $data['post_id'];

    // Check if the user already liked the post
    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Unlike if already liked
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->execute();

        // Get updated like count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = :post_id");
        $stmt->bindParam(':post_id', $post_id);
        $stmt->execute();
        $likes_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo json_encode([
            'success' => true, 
            'action' => 'unliked',
            'likes_count' => $likes_count
        ]);
    } else {
        // Like the post
        $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->execute();

        // Get updated like count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = :post_id");
        $stmt->bindParam(':post_id', $post_id);
        $stmt->execute();
        $likes_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo json_encode([
            'success' => true, 
            'action' => 'liked',
            'likes_count' => $likes_count
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>