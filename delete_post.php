<?php
// delete_post.php
session_start();
require_once __DIR__ . '/database.php';

// Check if user is logged in and the request is a POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$currentUserId = $_SESSION['user_id'];

if ($postId > 0) {
    // 1. Verify that the post exists and belongs to the logged-in user
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if ($post && (int)$post['user_id'] === (int)$currentUserId) {
        // 2. Delete the post (foreign keys with ON DELETE CASCADE will handle likes and comments)
        $deleteStmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $deleteStmt->execute([$postId]);
    }
}

// Redirect back to the feed
header('Location: index.php');
exit;