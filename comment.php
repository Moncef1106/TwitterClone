<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = $data['post_id'];
    $comment = $data['comment'];
    $user_id = $_SESSION['user_id'];

    // Insert comment into the database
    $stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (:user_id, :post_id, :comment)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':comment', $comment);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to comment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>