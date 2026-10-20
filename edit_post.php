<?php
// public/edit_post.php
session_start();
require_once __DIR__ . '/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];
$postId = (int)($_GET['id'] ?? $_POST['post_id'] ?? 0);
$error = '';

if ($postId <= 0) {
    header('Location: index.php');
    exit;
}

// 1. Fetch the post to verify ownership
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

// If post doesn't exist or current user is NOT the author, block access
if (!$post || (int)$post['user_id'] !== (int)$currentUserId) {
    header('Location: index.php');
    exit;
}

// 2. Handle form submission (Update action)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {
        $error = 'Post content cannot be empty.';
    } elseif (strlen($content) > 500) {
        $error = 'Post content must be under 500 characters.';
    } else {
        $updateStmt = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ?");
        $updateStmt->execute([$content, $postId]);

        // Redirect back to the single post view
        header("Location: post.php?id=$postId");
        exit;
    }
    // Keep user's input in case validation fails
    $post['content'] = $content;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post - EduSocial</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p><a href="post.php?id=<?= $postId ?>">← Cancel</a></p>
        <h2>Edit Post</h2>

        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="edit_post.php?id=<?= $postId ?>" method="POST">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <textarea name="content" rows="4" required style="width: 100%;"><?= htmlspecialchars($post['content']) ?></textarea><br>
            <button type="submit" style="margin-top: 10px;">Update Post</button>
        </form>
    </div>
</body>
</html>