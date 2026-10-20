<?php
// public/profile.php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];
$profileUserId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUserId;

if ($profileUserId <= 0) {
    header('Location: index.php');
    exit;
}

// 1. Fetch user profile details
$userStmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
$userStmt->execute([$profileUserId]);
$profileUser = $userStmt->fetch();

if (!$profileUser) {
    echo "User not found. <a href='index.php'>Return to Feed</a>";
    exit;
}

// 2. Fetch all posts authored by this user, including like counts
$postsStmt = $pdo->prepare("
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
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$postsStmt->execute([$currentUserId, $profileUserId]);
$posts = $postsStmt->fetchAll();

// Calculate total posts count
$totalPosts = count($posts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@<?= htmlspecialchars($profileUser['username']) ?> - Profile - EduSocial</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <p><a href="index.php">← Back to Feed</a></p>
        <hr>

        <!-- Profile Header -->
        <section style="background: #f4f4f4; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <h2>@<?= htmlspecialchars($profileUser['username']) ?></h2>
            <p style="color: #666; font-size: 0.9em; margin: 5px 0;">Member since: <?= htmlspecialchars($profileUser['created_at']) ?></p>
            <p style="font-weight: bold; margin-top: 10px;"><?= $totalPosts ?> Post(s) published</p>
        </section>

        <!-- User Posts Stream -->
        <section>
            <h3>Posts by @<?= htmlspecialchars($profileUser['username']) ?></h3>
            <?php if (empty($posts)): ?>
                <p style="color: #666;">This user hasn't posted anything yet.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                        <div>
                            <strong>@<?= htmlspecialchars($post['username']) ?></strong>
                            <a href="post.php?id=<?= $post['id'] ?>" style="color: gray; font-size: 0.8em; text-decoration: none;">
                                <?= htmlspecialchars($post['created_at']) ?> (View)
                            </a>
                        </div>
                        <p style="margin-top: 10px;"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                        <!-- Likes & Owner Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <form action="like.php" method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit">
                                    <?= $post['user_liked'] ? '❤️ Unlike' : '🤍 Like' ?>
                                </button>
                                <span><?= (int)$post['total_likes'] ?> like(s)</span>
                            </form>

                            <?php if ((int)$post['user_id'] === (int)$currentUserId): ?>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <a href="edit_post.php?id=<?= $post['id'] ?>" style="text-decoration: none; font-size: 0.9em;">✏️ Edit</a>
                                    <form action="delete_post.php" method="POST" onsubmit="return confirm('Delete this post?');" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                        <button type="submit" style="color: red; background: none; border: none; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>