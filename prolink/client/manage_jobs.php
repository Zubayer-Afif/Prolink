<?php
include('../includes/auth.php');
checkUser('client');
include('../config/db.php');

$client_id = $_SESSION['user_id'];

// Fetch all jobs by this client
$jobs = $conn->prepare("SELECT * FROM jobs WHERE user_id=? ORDER BY job_id DESC");
$jobs->bind_param("i", $client_id);
$jobs->execute();
$jobsResult = $jobs->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Jobs - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>

    <div class="container container-wide">
        <h2>Manage Your Jobs</h2>

        <?php if (isset($_GET['msg'])): ?>
            <div class="msg-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if ($jobsResult->num_rows === 0): ?>
            <p class="empty-state">You haven't posted any jobs yet.</p>
            <a href="post_job.php" class="btn post">Post Your First Job</a>
        <?php endif; ?>

        <?php while ($job = $jobsResult->fetch_assoc()):
            $jid = $job['job_id'];
            $status = $job['job_status'] ?? 'open';

            // Get proposals for this job
            $proposalsStmt = $conn->prepare("SELECT p.*, u.name, u.email FROM proposals p JOIN users u ON p.freelancer_id = u.user_id WHERE p.job_id=? ORDER BY p.proposal_id DESC");
            $proposalsStmt->bind_param("i", $jid);
            $proposalsStmt->execute();
            $proposalsResult = $proposalsStmt->get_result();

            // Check if client already reviewed for this job
            $revCheck = $conn->prepare("SELECT review_id FROM reviews WHERE job_id=? AND reviewer_id=?");
            $revCheck->bind_param("ii", $jid, $client_id);
            $revCheck->execute();
            $alreadyReviewed = $revCheck->get_result()->num_rows > 0;

            // Get accepted freelancer id
            $accProposal = $conn->prepare("SELECT freelancer_id FROM proposals WHERE job_id=? AND status='accepted'");
            $accProposal->bind_param("i", $jid);
            $accProposal->execute();
            $accResult = $accProposal->get_result();
            $acceptedFreelancer = ($accResult->num_rows > 0) ? $accResult->fetch_assoc()['freelancer_id'] : null;

            // Get contract if exists
            $contractStmt = $conn->prepare("SELECT * FROM contracts WHERE job_id=?");
            $contractStmt->bind_param("i", $jid);
            $contractStmt->execute();
            $contractResult = $contractStmt->get_result();
            $contract = ($contractResult->num_rows > 0) ? $contractResult->fetch_assoc() : null;
            ?>

            <div class="card">
                <div class="card-footer" style="margin-top:0; margin-bottom:12px;">
                    <h3 style="margin:0;"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <span class="badge badge-<?php echo str_replace('_', '-', $status); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                    </span>
                </div>

                <p><?php echo htmlspecialchars($job['description']); ?></p>

                <?php if (!empty($job['budget'])): ?>
                    <p style="font-size:13px; color:#9ca3af; margin-top:6px;">
                        Budget: <strong class="bid-amount">$<?php echo number_format($job['budget'], 2); ?></strong>
                        <?php if (!empty($job['skills'])): ?>
                            • Skills: <?php echo htmlspecialchars($job['skills']); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <!-- Contract info -->
                <?php if ($contract): ?>
                    <div
                        style="margin-top:12px; padding:12px; background:rgba(167,139,250,0.08); border-radius:8px; border:1px solid rgba(167,139,250,0.2);">
                        <p style="font-size:13px; margin-bottom:4px;">
                            <strong style="color:#c4b5fd;">Contract</strong>
                            — Payment: <strong class="bid-amount">$<?php echo number_format($contract['amount'], 2); ?></strong>
                            — Due: <strong
                                style="color:#fbbf24;"><?php echo date('M j, Y', strtotime($contract['due_date'])); ?></strong>
                            — Status: <span
                                class="badge badge-<?php echo ($contract['payment_status'] === 'paid') ? 'completed' : 'pending'; ?>">
                                <?php echo ucfirst($contract['payment_status']); ?>
                            </span>
                        </p>
                        <a href="view_contract.php?contract_id=<?php echo $contract['contract_id']; ?>" class="link-subtle">View
                            full contract →</a>
                    </div>
                <?php endif; ?>

                <!-- Proposals section -->
                <?php if ($proposalsResult->num_rows > 0): ?>
                    <h4 class="section-title" style="font-size:14px; margin-top:16px;">
                        Proposals (<?php echo $proposalsResult->num_rows; ?>)
                    </h4>

                    <?php while ($proposal = $proposalsResult->fetch_assoc()): ?>
                        <div class="bid-item">
                            <div class="bid-info">
                                <strong><?php echo htmlspecialchars($proposal['name'] ?? $proposal['email']); ?></strong>
                                — <span class="bid-amount">$<?php echo number_format($proposal['bid_amount'], 2); ?></span>
                                <br>
                                <small style="color:#6b7280;"><?php echo htmlspecialchars($proposal['cover_letter']); ?></small>
                            </div>
                            <div>
                                <?php if ($status === 'open' && ($proposal['status'] ?? 'pending') === 'pending'): ?>
                                    <a href="create_contract.php?job_id=<?php echo $jid; ?>&proposal_id=<?php echo $proposal['proposal_id']; ?>&freelancer_id=<?php echo $proposal['freelancer_id']; ?>"
                                        class="btn btn-sm post">Accept & Create Contract</a>
                                <?php else: ?>
                                    <span class="badge badge-<?php echo $proposal['status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($proposal['status'] ?? 'pending'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-state" style="padding:10px;">No proposals yet.</p>
                <?php endif; ?>

                <!-- Actions -->
                <div class="card-footer">
                    <div>
                        <?php if ($status === 'in_progress' && $contract): ?>
                            <a href="complete_job.php?job_id=<?php echo $jid; ?>&contract_id=<?php echo $contract['contract_id']; ?>"
                                class="btn btn-sm btn-amber">Complete & Pay</a>
                            <a href="../includes/messages.php?job_id=<?php echo $jid; ?>" class="btn btn-sm btn-message">💬
                                Messages</a>
                        <?php endif; ?>

                        <?php if ($status === 'completed' && $acceptedFreelancer && !$alreadyReviewed): ?>
                            <a href="review.php?job_id=<?php echo $jid; ?>&freelancer_id=<?php echo $acceptedFreelancer; ?>"
                                class="btn btn-sm btn-purple">Leave Review</a>
                        <?php elseif ($status === 'completed' && $alreadyReviewed): ?>
                            <span class="badge badge-completed">✓ Reviewed</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>

    </div>

</body>

</html>