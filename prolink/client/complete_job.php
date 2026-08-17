<?php
include('../includes/auth.php');
checkUser('client');
include('../config/db.php');

$client_id = $_SESSION['user_id'];

$job_id = intval($_GET['job_id'] ?? 0);
$contract_id = intval($_GET['contract_id'] ?? 0);

if ($job_id === 0 || $contract_id === 0) {
    header("Location: manage_jobs.php");
    exit();
}

// Verify job belongs to client and is in_progress
$jobStmt = $conn->prepare("SELECT * FROM jobs WHERE job_id=? AND user_id=? AND job_status='in_progress'");
$jobStmt->bind_param("ii", $job_id, $client_id);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();

if ($jobResult->num_rows === 0) {
    header("Location: manage_jobs.php");
    exit();
}

// Get contract
$contractStmt = $conn->prepare("SELECT c.*, uf.name AS freelancer_name, uf.email AS freelancer_email FROM contracts c JOIN users uf ON uf.user_id = c.freelancer_id WHERE c.contract_id=? AND c.client_id=?");
$contractStmt->bind_param("ii", $contract_id, $client_id);
$contractStmt->execute();
$contractResult = $contractStmt->get_result();

if ($contractResult->num_rows === 0) {
    header("Location: manage_jobs.php");
    exit();
}
$contract = $contractResult->fetch_assoc();

$message = "";
$msgType = "";

// Bank list
$banks = [
    "BRAC Bank",
    "City Bank",
    "Jamuna Bank",
    "Standard Chartered Bank",
    "EBL Bank",
    "Dutch Bangla Bank",
    "Bank Asia",
    "Mercantile Bank"
];

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $payment_method = $_POST['payment_method'];
    $payment_date = $_POST['payment_date'];
    $amount = $contract['amount'];

    if (!in_array($payment_method, $banks)) {
        $message = "Please select a valid payment method.";
        $msgType = "error";
    } else {
        // Create transaction
        $txStmt = $conn->prepare("INSERT INTO transactions (contract_id, amount, date, payment_method) VALUES (?, ?, ?, ?)");
        $txStmt->bind_param("idss", $contract_id, $amount, $payment_date, $payment_method);

        if ($txStmt->execute()) {
            // Update contract payment status
            $upContract = $conn->prepare("UPDATE contracts SET payment_status='paid' WHERE contract_id=?");
            $upContract->bind_param("i", $contract_id);
            $upContract->execute();

            // Mark job as completed
            $upJob = $conn->prepare("UPDATE jobs SET job_status='completed' WHERE job_id=?");
            $upJob->bind_param("i", $job_id);
            $upJob->execute();

            // Update client's total_spent
            $upSpent = $conn->prepare("UPDATE users SET total_spent = total_spent + ? WHERE user_id=?");
            $upSpent->bind_param("di", $amount, $client_id);
            $upSpent->execute();

            $txId = $conn->insert_id;
            $message = "Payment confirmed! Job marked as completed.";
            $msgType = "success";
        } else {
            $message = "Error processing payment: " . $conn->error;
            $msgType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Complete Job & Pay - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>

    <div class="container">
        <h2>Complete Job & Pay</h2>

        <div class="card" style="margin-bottom:20px;">
            <h3><?php echo htmlspecialchars($contract['title']); ?></h3>
            <p style="font-size:14px;">
                <strong>Freelancer:</strong>
                <?php echo htmlspecialchars($contract['freelancer_name'] ?? $contract['freelancer_email']); ?>
            </p>
            <p style="font-size:14px;">
                Due: <?php echo date('M j, Y', strtotime($contract['due_date'])); ?>
            </p>
            <hr class="divider">
            <p style="font-size:20px; text-align:center; color:#34d399; font-weight:700;">
                Payment Amount: $<?php echo number_format($contract['amount'], 2); ?>
            </p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="msg-<?php echo $msgType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($msgType !== 'success'): ?>
            <form method="POST">
                <label>Payment Date</label>
                <input type="date" name="payment_date" required value="<?php echo date('Y-m-d'); ?>">

                <label>Payment Method</label>
                <select name="payment_method" required>
                    <option value="">-- Select Bank --</option>
                    <?php foreach ($banks as $bank): ?>
                        <option value="<?php echo $bank; ?>"><?php echo $bank; ?></option>
                    <?php endforeach; ?>
                </select>

                <div
                    style="margin-top:16px; padding:12px; background:rgba(251,191,36,0.08); border-radius:8px; border:1px solid rgba(251,191,36,0.2);">
                    <p style="font-size:13px; color:#fbbf24;">
                        ⚠ By confirming, the payment will be processed and the job will be marked as completed.
                    </p>
                </div>

                <button type="submit" class="btn btn-amber" style="width:100%; margin-top:16px;">Confirm Payment</button>
            </form>
        <?php else: ?>
            <div style="margin-top:16px;">
                <a href="manage_jobs.php" class="btn btn-outline">← Back to Manage Jobs</a>
                <a href="view_contract.php?contract_id=<?php echo $contract_id; ?>" class="btn btn-purple">View Contract</a>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>