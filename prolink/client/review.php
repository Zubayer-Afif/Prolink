<?php
include('../includes/auth.php');
checkUser('client');
include('../config/db.php');

$client_id = $_SESSION['user_id'];
$message = "";
$msgType = "";

$job_id = intval($_GET['job_id'] ?? 0);
$freelancer_id = intval($_GET['freelancer_id'] ?? 0);

if ($job_id === 0 || $freelancer_id === 0) {
    header("Location: manage_jobs.php");
    exit();
}

// Verify: job belongs to client, is completed, freelancer has accepted proposal
$verify = $conn->prepare("
    SELECT j.title, u.name AS freelancer_name, u.email AS freelancer_email
    FROM jobs j
    JOIN proposals p ON p.job_id = j.job_id AND p.status = 'accepted'
    JOIN users u ON u.user_id = p.freelancer_id
    WHERE j.job_id = ? AND j.user_id = ? AND j.job_status = 'completed' AND p.freelancer_id = ?
");
$verify->bind_param("iii", $job_id, $client_id, $freelancer_id);
$verify->execute();
$result = $verify->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_jobs.php");
    exit();
}
$jobData = $result->fetch_assoc();

// Check if already reviewed
$revCheck = $conn->prepare("SELECT review_id FROM reviews WHERE job_id=? AND reviewer_id=? AND reviewee_id=?");
$revCheck->bind_param("iii", $job_id, $client_id, $freelancer_id);
$revCheck->execute();
if ($revCheck->get_result()->num_rows > 0) {
    $message = "You have already reviewed this freelancer for this job.";
    $msgType = "error";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message)) {
    $rating_score = intval($_POST['rating_score'] ?? 0);
    $comments = trim($_POST['comments'] ?? '');

    if ($rating_score < 1 || $rating_score > 5) {
        $message = "Please select a rating between 1 and 5 stars.";
        $msgType = "error";
    } else {
        $commentsValue = (!empty($comments)) ? $comments : null;
        $stmt = $conn->prepare("INSERT INTO reviews (job_id, reviewer_id, reviewee_id, reviewer_type, rating_score, comments) VALUES (?, ?, ?, 'client', ?, ?)");
        $stmt->bind_param("iiiis", $job_id, $client_id, $freelancer_id, $rating_score, $commentsValue);

        if ($stmt->execute()) {
            $message = "Review submitted successfully!";
            $msgType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $msgType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Review Freelancer - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>
    <div class="container">
        <h2>Review Freelancer</h2>
        <div class="card" style="margin-bottom:20px;">
            <p><strong>Job:</strong> <?php echo htmlspecialchars($jobData['title']); ?></p>
            <p><strong>Freelancer:</strong>
                <?php echo htmlspecialchars($jobData['freelancer_name'] ?? $jobData['freelancer_email']); ?></p>
        </div>
        <?php if (!empty($message)): ?>
            <div class="msg-<?php echo $msgType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($msgType !== 'success' && $msgType !== 'error'): ?>
            <form method="POST">
                <label style="text-align:center;">How would you rate this freelancer?</label>
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating_score" value="5"><label for="star5">★</label>
                    <input type="radio" id="star4" name="rating_score" value="4"><label for="star4">★</label>
                    <input type="radio" id="star3" name="rating_score" value="3"><label for="star3">★</label>
                    <input type="radio" id="star2" name="rating_score" value="2"><label for="star2">★</label>
                    <input type="radio" id="star1" name="rating_score" value="1"><label for="star1">★</label>
                </div>
                <label>Comments (optional)</label>
                <textarea name="comments" placeholder="Share your experience..."></textarea>
                <button type="submit" class="btn btn-purple" style="width:100%; margin-top:12px;">Submit Review</button>
            </form>
        <?php endif; ?>
        <?php if ($msgType === 'success'): ?>
            <a href="manage_jobs.php" class="btn btn-outline" style="margin-top:16px;">← Back to Manage Jobs</a>
        <?php endif; ?>
    </div>
</body>

</html>