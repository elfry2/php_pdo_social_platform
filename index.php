<?php
// index.php
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user_id'];

// --- Pagination Setup ---
$postsPerPage = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $postsPerPage;

// Count total posts for page calculations
$totalStmt = $pdo->query("SELECT COUNT(*) FROM posts");
$totalPosts = (int) $totalStmt->fetchColumn();
$totalPages = ceil($totalPosts / $postsPerPage);

// Query paginated posts + count total likes + check if current user liked (using all named parameters)
$postsStmt = $pdo->prepare("
    SELECT 
        p.id, 
        p.content, 
        p.created_at, 
        p.user_id,
        u.username,
        COUNT(DISTINCT l.user_id) AS total_likes,
        SUM(CASE WHEN l.user_id = :current_user_id THEN 1 ELSE 0 END) AS user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN likes l ON p.id = l.post_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");

// Bind all parameters using named placeholders
$postsStmt->bindValue(':current_user_id', $currentUserId, PDO::PARAM_INT);
$postsStmt->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
$postsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$postsStmt->execute();
$posts = $postsStmt->fetchAll();

// Fetch comments for all displayed posts on current page
$commentsByPost = [];
if (!empty($posts)) {
    $postIds = array_column($posts, 'id');
    $inClause = implode(',', array_fill(0, count($postIds), '?'));

    $commentsStmt = $pdo->prepare("
        SELECT c.post_id, c.content, c.created_at, u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id IN ($inClause)
        ORDER BY c.created_at ASC
    ");
    $commentsStmt->execute($postIds);

    foreach ($commentsStmt->fetchAll() as $comment) {
        $commentsByPost[$comment['post_id']][] = $comment;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Feed - EduSocial</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <header style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Welcome, <a href="profile.php" style="text-decoration: none;"><?= htmlspecialchars($_SESSION['username']) ?></a></h2>
            <div>
                <a href="profile.php" style="margin-right: 15px;">My Profile</a>
                <a href="logout.php">Log Out</a>
            </div>
        </header>

        <hr>

        <!-- Create Post Navigation Button -->
        <div style="margin: 20px 0; text-align: right;">
            <a href="create_post.php" style="padding: 10px 18px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">➕ Create New Post</a>
        </div>

        <hr>

        <!-- Feed List -->
        <section>
            <h3>Feed</h3>
            <?php if (empty($posts)): ?>
                <p>No posts yet. <a href="create_post.php">Be the first to share something!</a></p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                        <div>
                            <a href="profile.php?id=<?= $post['user_id'] ?>" style="text-decoration: none; color: inherit; font-weight: bold;">
                                @<?= htmlspecialchars($post['username']) ?>
                            </a>
                            <a href="post.php?id=<?= $post['id'] ?>" style="color: gray; font-size: 0.8em; text-decoration: none; margin-left: 5px;">
                                <?= htmlspecialchars($post['created_at']) ?> (View)
                            </a>
                        </div>
                        <p style="margin-top: 10px;"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                        <!-- Like & Post Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
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
                                    <a href="edit_post.php?id=<?= $post['id'] ?>" style="text-decoration: none; font-size: 0.9em;">✏️ Edit</a>

                                    <form action="delete_post.php" method="POST" onsubmit="return confirm('Delete this post?');" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                        <button type="submit" style="color: red; background: none; border: none; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Comments Section -->
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                            <strong>Comments:</strong>
                            <?php $postComments = $commentsByPost[$post['id']] ?? []; ?>
                            <?php if (empty($postComments)): ?>
                                <p style="font-size: 0.9em; color: #666;">No comments yet.</p>
                            <?php else: ?>
                                <?php foreach ($postComments as $comment): ?>
                                    <div style="font-size: 0.9em; margin-top: 5px;">
                                        <strong>@<?= htmlspecialchars($comment['username']) ?>:</strong>
                                        <?= htmlspecialchars($comment['content']) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Add Comment Form -->
                            <form action="comment.php" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <input type="text" name="content" placeholder="Write a comment..." required style="width: 70%;">
                                <button type="submit">Reply</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>

                <!-- Pagination Navigation -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                    <div>
                        <?php if ($page > 1): ?>
                            <a href="index.php?page=<?= $page - 1 ?>" style="padding: 6px 12px; border: 1px solid #ccc; text-decoration: none; border-radius: 4px;">← Previous</a>
                        <?php endif; ?>
                    </div>

                    <span>Page <?= $page ?> of <?= max(1, $totalPages) ?></span>

                    <div>
                        <?php if ($page < $totalPages): ?>
                            <a href="index.php?page=<?= $page + 1 ?>" style="padding: 6px 12px; border: 1px solid #ccc; text-decoration: none; border-radius: 4px;">Next →</a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endif; ?>
        </section>
    </div>
</body>

</html>