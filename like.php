<?php
// public/like.php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($postId > 0) {
    // Check if user already liked the post
    $checkStmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
    $checkStmt->execute([$userId, $postId]);

    if ($checkStmt->fetch()) {
        // Unlike: Remove record
        $deleteStmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $deleteStmt->execute([$userId, $postId]);
    } else {
        // Like: Insert record
        $insertStmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $insertStmt->execute([$userId, $postId]);
    }
}

// Redirect back to feed
header('Location: index.php');
exit;