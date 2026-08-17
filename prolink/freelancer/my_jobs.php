<?php
include('../includes/auth.php');
checkUser('freelancer');
include('../config/db.php');

$freelancer_id = $_SESSION['user_id'];

$jobs = $conn->prepare("
    SELECT j.*, p.bid_amount, p.status AS proposal_status,
           u.name AS client_name, u.email AS client_email,
           c.contract_id, c.amount, c.due_date, c.payment_status
    FROM proposals p
    JOIN jobs j ON j.job_id = p.job_id
    JOIN users u ON u.user_id = j.user_id
    LEFT JOIN contracts c ON c.job_id = j.job_id
    WHERE p.freelancer_id = ? AND p.status = 'accepted'
    ORDER BY j.job_id DESC
");
$jobs->bind_param("i", $freelancer_id);
$jobs->execute();
$jobsResult = $jobs->get_result();
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Jobs - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>
    <div class="container container-wide">
        <h2>My Accepted Jobs</h2>
        <?php if ($jobsResult->num_rows === 0): ?>
            <p class="empty-state">No accepted jobs yet. Keep bidding!</p>
            <a href="view_jobs.php" class="btn post">Browse Jobs</a>
        <?php endif; ?>
        <?php while ($job = $jobsResult->fetch_assoc()):
            $jid = $job['job_id'];
            $status = $job['job_status'] ?? 'open';
            $client_id = $job['user_id'];

            $revCheck = $conn->prepare("SELECT review_id FROM reviews WHERE job_id=? AND reviewer_id=? AND reviewee_id=?");
            $revCheck->bind_param("iii", $jid, $freelancer_id, $client_id);
            $revCheck->execute();
            $alreadyReviewed = $revCheck->get_result()->num_rows > 0;
            ?>
            <div class="card">
                <div class="card-footer" style="margin-top:0; margin-bottom:8px;">
                    <h3 style="margin:0;"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <span class="badge badge-<?php echo str_replace('_', '-', $status); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                    </span>
                </div>
                <p><?php echo htmlspecialchars($job['description']); ?></p>
                <div style="margin-top:10px; font-size:13px; color:#9ca3af;">
                    Client: <strong
                        style="color:#c4b5fd;"><?php echo htmlspecialchars($job['client_name'] ?? $job['client_email']); ?></strong>
                    • Bid: <strong class="bid-amount">$<?php echo number_format($job['bid_amount'], 2); ?></strong>
                </div>
                <?php if ($job['contract_id']): ?>
                    <div
                        style="margin-top:12px; padding:12px; background:rgba(167,139,250,0.08); border-radius:8px; border:1px solid rgba(167,139,250,0.2);">
                        <p style="font-size:13px; margin-bottom:0;">
                            <strong style="color:#c4b5fd;">Contract</strong>
                            — Payment: <strong class="bid-amount">$<?php echo number_format($job['amount'], 2); ?></strong>
                            — Due: <strong
                                style="color:#fbbf24;"><?php echo date('M j, Y', strtotime($job['due_date'])); ?></strong>
                            — <span
                                class="badge badge-<?php echo ($job['payment_status'] === 'paid') ? 'completed' : 'pending'; ?>">
                                <?php echo ucfirst($job['payment_status']); ?>
                            </span>
                        </p>
                    </div>
                <?php endif; ?>
                <div class="card-footer">
                    <?php if ($status === 'completed' && !$alreadyReviewed): ?>
                        <a href="review.php?job_id=<?php echo $jid; ?>&client_id=<?php echo $client_id; ?>"
                            class="btn btn-sm btn-purple">Leave Review</a>
                    <?php elseif ($status === 'completed' && $alreadyReviewed): ?>
                        <span class="badge badge-completed">✓ Reviewed</span>
                    <?php elseif ($status === 'in_progress'): ?>
                        <span style="font-size:13px; color:#fbbf24;">⏳ Waiting for client to complete & pay</span>
                        <a href="../includes/messages.php?job_id=<?php echo $jid; ?>" class="btn btn-sm btn-message">💬
                            Messages</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>