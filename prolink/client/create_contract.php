<?php
include('../includes/auth.php');
checkUser('client');
include('../config/db.php');

$client_id = $_SESSION['user_id'];

$job_id = intval($_GET['job_id'] ?? 0);
$proposal_id = intval($_GET['proposal_id'] ?? 0);
$freelancer_id = intval($_GET['freelancer_id'] ?? 0);

if ($job_id === 0 || $proposal_id === 0 || $freelancer_id === 0) {
    header("Location: manage_jobs.php");
    exit();
}

// Verify job belongs to client and is open
$jobStmt = $conn->prepare("SELECT * FROM jobs WHERE job_id=? AND user_id=? AND job_status='open'");
$jobStmt->bind_param("ii", $job_id, $client_id);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();

if ($jobResult->num_rows === 0) {
    header("Location: manage_jobs.php");
    exit();
}
$job = $jobResult->fetch_assoc();

// Get proposal info
$proposalStmt = $conn->prepare("SELECT p.*, u.name, u.email FROM proposals p JOIN users u ON u.user_id = p.freelancer_id WHERE p.proposal_id=? AND p.job_id=? AND p.freelancer_id=?");
$proposalStmt->bind_param("iii", $proposal_id, $job_id, $freelancer_id);
$proposalStmt->execute();
$proposalResult = $proposalStmt->get_result();

if ($proposalResult->num_rows === 0) {
    header("Location: manage_jobs.php");
    exit();
}
$proposal = $proposalResult->fetch_assoc();

$message = "";
$msgType = "";

// Handle form submission — create contract
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $due_date = $_POST['due_date'];
    $amount = $proposal['bid_amount'];

    // Check no other proposal is already accepted
    $existing = $conn->prepare("SELECT proposal_id FROM proposals WHERE job_id=? AND status='accepted'");
    $existing->bind_param("i", $job_id);
    $existing->execute();

    if ($existing->get_result()->num_rows > 0) {
        $message = "A proposal has already been accepted for this job.";
        $msgType = "error";
    } else {
        // Accept the proposal
        $stmt = $conn->prepare("UPDATE proposals SET status='accepted' WHERE proposal_id=?");
        $stmt->bind_param("i", $proposal_id);
        $stmt->execute();

        // Reject other pending proposals
        $stmt2 = $conn->prepare("UPDATE proposals SET status='rejected' WHERE job_id=? AND proposal_id!=? AND status='pending'");
        $stmt2->bind_param("ii", $job_id, $proposal_id);
        $stmt2->execute();

        // Update job status
        $stmt3 = $conn->prepare("UPDATE jobs SET job_status='in_progress' WHERE job_id=?");
        $stmt3->bind_param("i", $job_id);
        $stmt3->execute();

        // Create contract
        $contractStmt = $conn->prepare("INSERT INTO contracts (job_id, client_id, freelancer_id, title, description, amount, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $contractStmt->bind_param("iiissds", $job_id, $client_id, $freelancer_id, $job['title'], $job['description'], $amount, $due_date);

        if ($contractStmt->execute()) {
            $contractId = $conn->insert_id;
            header("Location: view_contract.php?contract_id=" . $contractId . "&new=1");
            exit();
        } else {
            $message = "Error creating contract: " . $conn->error;
            $msgType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Contract - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>

    <div class="container">
        <h2>Create Contract</h2>

        <div class="card" style="margin-bottom:20px;">
            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
            <p><?php echo htmlspecialchars($job['description']); ?></p>
            <hr class="divider">
            <p style="font-size:14px;">
                <strong>Freelancer:</strong> <?php echo htmlspecialchars($proposal['name'] ?? $proposal['email']); ?>
            </p>
            <p style="font-size:14px;">
                <strong>Bid Amount:</strong> <span
                    class="bid-amount">$<?php echo number_format($proposal['bid_amount'], 2); ?></span>
            </p>
            <p style="font-size:14px;">
                <strong>Cover Letter:</strong> <?php echo htmlspecialchars($proposal['cover_letter']); ?>
            </p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="msg-<?php echo $msgType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Due Date</label>
            <input type="date" name="due_date" required min="<?php echo date('Y-m-d'); ?>">

            <div
                style="margin-top:16px; padding:12px; background:rgba(52,211,153,0.08); border-radius:8px; border:1px solid rgba(52,211,153,0.2);">
                <p style="font-size:14px; color:#34d399;">
                    Payment amount will be set to the bid amount:
                    <strong>$<?php echo number_format($proposal['bid_amount'], 2); ?></strong>
                </p>
            </div>

            <button type="submit" class="btn post" style="width:100%; margin-top:16px;">Accept Bid & Create
                Contract</button>
        </form>

        <a href="manage_jobs.php" class="link-subtle" style="display:block; text-align:center; margin-top:16px;">← Back
            to Manage Jobs</a>
    </div>

</body>

</html>