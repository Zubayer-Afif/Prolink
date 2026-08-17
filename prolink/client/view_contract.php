<?php
include('../includes/auth.php');
checkUser('client');
include('../config/db.php');

$client_id = $_SESSION['user_id'];
$contract_id = intval($_GET['contract_id'] ?? 0);

if ($contract_id === 0) {
    header("Location: manage_jobs.php");
    exit();
}

// Fetch contract with related info
$stmt = $conn->prepare("
    SELECT c.*, 
           uc.name AS client_name, uc.email AS client_email,
           uf.name AS freelancer_name, uf.email AS freelancer_email
    FROM contracts c
    JOIN users uc ON uc.user_id = c.client_id
    JOIN users uf ON uf.user_id = c.freelancer_id
    WHERE c.contract_id = ? AND c.client_id = ?
");
$stmt->bind_param("ii", $contract_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_jobs.php");
    exit();
}

$contract = $result->fetch_assoc();

// Get transaction if exists
$txStmt = $conn->prepare("SELECT * FROM transactions WHERE contract_id=?");
$txStmt->bind_param("i", $contract_id);
$txStmt->execute();
$txResult = $txStmt->get_result();
$transaction = ($txResult->num_rows > 0) ? $txResult->fetch_assoc() : null;

$isNew = isset($_GET['new']);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Contract Details - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>

    <div class="container container-wide">

        <?php if ($isNew): ?>
            <div class="msg-success">✅ Contract created successfully! Bid accepted and job is now in progress.</div>
        <?php endif; ?>

        <h2>Contract Details</h2>

        <div class="card" style="text-align:left;">
            <div class="profile-info">
                <div class="profile-info-row">
                    <span class="profile-info-label">Job Title</span>
                    <span class="profile-info-value"><?php echo htmlspecialchars($contract['title']); ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Description</span>
                    <span class="profile-info-value"><?php echo htmlspecialchars($contract['description']); ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Client</span>
                    <span
                        class="profile-info-value"><?php echo htmlspecialchars($contract['client_name'] ?? $contract['client_email']); ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Freelancer</span>
                    <span
                        class="profile-info-value"><?php echo htmlspecialchars($contract['freelancer_name'] ?? $contract['freelancer_email']); ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Amount</span>
                    <span class="profile-info-value"
                        style="color:#34d399; font-weight:700;">$<?php echo number_format($contract['amount'], 2); ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Due Date</span>
                    <span class="profile-info-value"
                        style="color:#fbbf24;"><?php echo date('F j, Y', strtotime($contract['due_date'])); ?></span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Payment Status</span>
                    <span class="profile-info-value">
                        <span
                            class="badge badge-<?php echo ($contract['payment_status'] === 'paid') ? 'completed' : 'pending'; ?>">
                            <?php echo ucfirst($contract['payment_status']); ?>
                        </span>
                    </span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Created</span>
                    <span
                        class="profile-info-value"><?php echo date('F j, Y g:i A', strtotime($contract['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Transaction details if paid -->
        <?php if ($transaction): ?>
            <h3 class="section-title">Transaction Details</h3>
            <div class="card" style="text-align:left;">
                <div class="profile-info">

                    <div class="profile-info-row">
                        <span class="profile-info-label">Amount</span>
                        <span class="profile-info-value"
                            style="color:#34d399;">$<?php echo number_format($transaction['amount'], 2); ?></span>
                    </div>
                    <div class="profile-info-row">
                        <span class="profile-info-label">Payment Date</span>
                        <span
                            class="profile-info-value"><?php echo date('F j, Y', strtotime($transaction['date'])); ?></span>
                    </div>
                    <div class="profile-info-row">
                        <span class="profile-info-label">Payment Method</span>
                        <span
                            class="profile-info-value"><?php echo htmlspecialchars($transaction['payment_method']); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top:20px;">
            <a href="manage_jobs.php" class="btn btn-outline">← Back to Manage Jobs</a>
        </div>

    </div>

</body>

</html>