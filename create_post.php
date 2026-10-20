<?php
// create_post.php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {
        $error = 'Post content cannot be empty.';
    } elseif (strlen($content) > 500) {
        $error = 'Post content must be under 500 characters.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $content])) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Failed to create post. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post - EduSocial</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p><a href="index.php">← Back to Feed</a></p>
        <hr>

        <h2>Create a New Post</h2>

        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="create_post.php" method="POST">
            <textarea name="content" rows="4" placeholder="What's on your mind?" required style="width: 100%; padding: 8px;"></textarea><br><br>
            <button type="submit" style="padding: 8px 16px;">Publish Post</button>
        </form>
    </div>
</body>
</html>