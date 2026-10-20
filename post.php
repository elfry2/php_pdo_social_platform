<?php
// public/post.php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];
$postId = (int) ($_GET['id'] ?? 0);

if ($postId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch single post details with likes count
$stmt = $pdo->prepare("
    SELECT 
        p.id, 
        p.content, 
        p.created_at, 
        p.user_id,
        u.username,
        COUNT(DISTINCT l.user_id) AS total_likes,
        SUM(CASE WHEN l.user_id = ? THEN 1 ELSE 0 END) AS user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN likes l ON p.id = l.post_id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->execute([$currentUserId, $postId]);
$post = $stmt->fetch();

if (!$post) {
    echo "Post not found or has been deleted. <a href='index.php'>Return to Feed</a>";
    exit;
}

// Fetch all comments for this post
$commentsStmt = $pdo->prepare("
    SELECT c.id, c.content, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$commentsStmt->execute([$postId]);
$comments = $commentsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Post by @<?= htmlspecialchars($post['username']) ?> - EduSocial</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p><a href="index.php">← Back to Feed</a></p>
        <hr>

        <!-- Main Post -->
        <article style="border: 1px solid #ccc; padding: 20px; border-radius: 5px;">

            <a href="profile.php?id=<?= $post['user_id'] ?>"
                style="text-decoration: none; color: inherit; font-weight: bold;">
                @
                <?= htmlspecialchars($post['username']) ?>
            </a>
            
            <span style="color: gray; font-size: 0.8em;"><?= htmlspecialchars($post['created_at']) ?></span>
            <p style="font-size: 1.1em; margin-top: 15px;"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                <div>
                    <form action="like.php" method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit">
                            <?= $post['user_liked'] ? '❤️ Unlike' : '🤍 Like' ?>
                        </button>
                    </form>
                    <span><?= (int) $post['total_likes'] ?> like(s)</span>
                </div>

                <?php if ((int) $post['user_id'] === (int) $currentUserId): ?>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="edit_post.php?id=<?= $post['id'] ?>" style="text-decoration: none; font-size: 0.9em;">✏️
                            Edit</a>
                        <form action="delete_post.php" method="POST" onsubmit="return confirm('Delete this post?');"
                            style="display: inline;">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit"
                                style="color: red; background: none; border: none; cursor: pointer;">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </article>

        <!-- Comments -->
        <section style="margin-top: 20px;">
            <h3>Comments</h3>
            <?php if (empty($comments)): ?>
                <p style="color: #666;">No comments yet.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div style="background: #f9f9f9; padding: 10px; margin-bottom: 8px; border-radius: 4px;">
                        <strong>@<?= htmlspecialchars($comment['username']) ?>:</strong>
                        <span><?= htmlspecialchars($comment['content']) ?></span>
                        <div style="font-size: 0.75em; color: #888; margin-top: 2px;">
                            <?= htmlspecialchars($comment['created_at']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <form action="comment.php" method="POST" style="margin-top: 15px;">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="text" name="content" placeholder="Write a comment..." required
                    style="width: 75%; padding: 6px;">
                <button type="submit" style="padding: 6px 12px;">Reply</button>
            </form>
        </section>
    </div>
</body>

</html>