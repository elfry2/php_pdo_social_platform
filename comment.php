<?php
// public/comment.php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$postId  = (int)($_POST['post_id'] ?? 0);
$userId  = $_SESSION['user_id'];
$content = trim($_POST['content'] ?? '');

if ($postId > 0 && !empty($content)) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $userId, $content]);
}

header('Location: index.php');
exit;