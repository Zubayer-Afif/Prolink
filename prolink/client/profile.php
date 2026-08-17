<?php
include('../includes/auth.php');
checkUser('client');
include('../config/db.php');

$client_id = $_SESSION['user_id'];

$userStmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$userStmt->bind_param("i", $client_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$ratingStmt = $conn->prepare("SELECT AVG(rating_score) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE reviewee_id=?");
$ratingStmt->bind_param("i", $client_id);
$ratingStmt->execute();
$ratingData = $ratingStmt->get_result()->fetch_assoc();
$avgRating = round($ratingData['avg_rating'] ?? 0, 1);
$totalReviews = $ratingData['total_reviews'] ?? 0;

$jobsStmt = $conn->prepare("SELECT COUNT(*) as total_jobs FROM jobs WHERE user_id=?");
$jobsStmt->bind_param("i", $client_id);
$jobsStmt->execute();
$totalJobs = $jobsStmt->get_result()->fetch_assoc()['total_jobs'];

$totalSpent = $user['total_spent'] ?? 0;

$reviewsStmt = $conn->prepare("
    SELECT r.*, u.name AS reviewer_name, u.email AS reviewer_email, j.title AS job_title
    FROM reviews r JOIN users u ON u.user_id = r.reviewer_id JOIN jobs j ON j.job_id = r.job_id
    WHERE r.reviewee_id = ? ORDER BY r.review_date DESC LIMIT 5
");
$reviewsStmt->bind_param("i", $client_id);
$reviewsStmt->execute();
$reviewsResult = $reviewsStmt->get_result();

function renderStars($rating)
{
    $html = '<span class="stars-display">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= ($i <= $rating) ? '★' : '<span class="star-empty">★</span>';
    }
    return $html . '</span>';
}

$initial = strtoupper(substr($user['name'] ?? $user['email'], 0, 1));
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Profile - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>
    <div class="container container-wide">
        <div class="profile-header">
            <div class="profile-avatar"><?php echo $initial; ?></div>
            <div class="profile-name"><?php echo htmlspecialchars($user['name'] ?? 'Client'); ?></div>
            <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
            <div style="margin-top:8px;">
                <?php echo renderStars(round($avgRating)); ?>
                <span style="color:#9ca3af; font-size:13px; margin-left:4px;"><?php echo $avgRating; ?>/5
                    (<?php echo $totalReviews; ?> reviews)</span>
            </div>
        </div>
        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-value"><?php echo $totalJobs; ?></div>
                <div class="stat-label">Projects Posted</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">$<?php echo number_format($totalSpent, 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $avgRating; ?></div>
                <div class="stat-label">Avg Rating</div>
            </div>
        </div>
        <div class="profile-info">
            <div class="profile-info-row"><span class="profile-info-label">Name</span><span
                    class="profile-info-value"><?php echo htmlspecialchars($user['name'] ?? '—'); ?></span></div>
            <div class="profile-info-row"><span class="profile-info-label">Email</span><span
                    class="profile-info-value"><?php echo htmlspecialchars($user['email']); ?></span></div>
            <div class="profile-info-row"><span class="profile-info-label">Company</span><span
                    class="profile-info-value"><?php echo htmlspecialchars($user['company_name'] ?? '—'); ?></span>
            </div>
            <div class="profile-info-row"><span class="profile-info-label">Account Type</span><span
                    class="profile-info-value">Client</span></div>
        </div>
        <h3 class="section-title">Recent Reviews</h3>
        <?php if ($reviewsResult->num_rows === 0): ?>
            <p class="empty-state">No reviews yet.</p>
        <?php else: ?>
            <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-card-header">
                        <span
                            class="review-card-author"><?php echo htmlspecialchars($review['reviewer_name'] ?? $review['reviewer_email']); ?></span>
                        <?php echo renderStars($review['rating_score']); ?>
                    </div>
                    <?php if (!empty($review['comments'])): ?>
                        <div class="review-card-comment">"<?php echo htmlspecialchars($review['comments']); ?>"</div>
                    <?php endif; ?>
                    <div class="review-card-job">Job: <?php echo htmlspecialchars($review['job_title']); ?> •
                        <?php echo date('M j, Y', strtotime($review['review_date'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>

</html>