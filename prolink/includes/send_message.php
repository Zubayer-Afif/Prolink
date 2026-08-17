<?php
include('../includes/auth.php');
include('../config/db.php');

// Only handle POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$sender_id = $_SESSION['user_id'];
$job_id = intval($_POST['job_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($job_id === 0 || $content === '') {
    header("Location: messages.php?job_id=" . $job_id . "&error=empty");
    exit();
}

// Verify job exists and is in_progress
$jobStmt = $conn->prepare("SELECT j.user_id AS client_id, p.freelancer_id
    FROM jobs j
    JOIN proposals p ON p.job_id = j.job_id AND p.status = 'accepted'
    WHERE j.job_id = ? AND j.job_status = 'in_progress'");
$jobStmt->bind_param("i", $job_id);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();

if ($jobResult->num_rows === 0) {
    header("Location: messages.php?job_id=" . $job_id . "&error=invalid");
    exit();
}

$jobData = $jobResult->fetch_assoc();
$client_id = $jobData['client_id'];
$freelancer_id = $jobData['freelancer_id'];

// Verify the sender is either the client or the freelancer
if ($sender_id != $client_id && $sender_id != $freelancer_id) {
    header("Location: ../index.php");
    exit();
}

// Determine receiver
$receiver_id = ($sender_id == $client_id) ? $freelancer_id : $client_id;

// Insert into messages table
$msgStmt = $conn->prepare("INSERT INTO messages (content) VALUES (?)");
$msgStmt->bind_param("s", $content);

if ($msgStmt->execute()) {
    $message_id = $conn->insert_id;

    // Insert into send_message table
    $smStmt = $conn->prepare("INSERT INTO send_message (message_id, sender_id, receiver_id, job_id) VALUES (?, ?, ?, ?)");
    $smStmt->bind_param("iiii", $message_id, $sender_id, $receiver_id, $job_id);
    $smStmt->execute();
}

header("Location: messages.php?job_id=" . $job_id);
exit();
?>