<?php
include('../includes/auth.php');
include('../config/db.php');

$current_user = $_SESSION['user_id'];
$user_type = $_SESSION['type'];
$job_id = intval($_GET['job_id'] ?? 0);

if ($job_id === 0) {
    header("Location: ../index.php");
    exit();
}

// Verify job exists and is in_progress, get both parties
$jobStmt = $conn->prepare("
    SELECT j.*, j.user_id AS client_id, p.freelancer_id,
           uc.name AS client_name, uc.email AS client_email,
           uf.name AS freelancer_name, uf.email AS freelancer_email
    FROM jobs j
    JOIN proposals p ON p.job_id = j.job_id AND p.status = 'accepted'
    JOIN users uc ON uc.user_id = j.user_id
    JOIN users uf ON uf.user_id = p.freelancer_id
    WHERE j.job_id = ? AND j.job_status = 'in_progress'
");
$jobStmt->bind_param("i", $job_id);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();

if ($jobResult->num_rows === 0) {
    echo "<!DOCTYPE html><html><head><title>Error - ProLink</title><link rel='stylesheet' href='../css/style.css'></head><body><div class='container'><h2>Access Denied</h2><p>This job is not currently active or does not exist.</p><a href='../index.php' class='btn btn-purple'>Go Home</a></div></body></html>";
    exit();
}

$job = $jobResult->fetch_assoc();
$client_id = $job['client_id'];
$freelancer_id = $job['freelancer_id'];

// Verify the current user is either the client or the freelancer
if ($current_user != $client_id && $current_user != $freelancer_id) {
    echo "<!DOCTYPE html><html><head><title>Error - ProLink</title><link rel='stylesheet' href='../css/style.css'></head><body><div class='container'><h2>Access Denied</h2><p>You are not authorized to view these messages.</p><a href='../index.php' class='btn btn-purple'>Go Home</a></div></body></html>";
    exit();
}

// Determine the other party's name
if ($current_user == $client_id) {
    $other_name = $job['freelancer_name'] ?? $job['freelancer_email'];
    $back_link = '../client/manage_jobs.php';
} else {
    $other_name = $job['client_name'] ?? $job['client_email'];
    $back_link = '../freelancer/my_jobs.php';
}

// Fetch all messages for this job
$msgStmt = $conn->prepare("
    SELECT m.message_id, m.content, m.timestamp, sm.sender_id, sm.receiver_id,
           u.name AS sender_name, u.email AS sender_email
    FROM messages m
    JOIN send_message sm ON sm.message_id = m.message_id
    JOIN users u ON u.user_id = sm.sender_id
    WHERE sm.job_id = ?
    ORDER BY m.timestamp ASC
");
$msgStmt->bind_param("i", $job_id);
$msgStmt->execute();
$messagesResult = $msgStmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Messages — <?php echo htmlspecialchars($job['title']); ?> — ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>

    <div class="container container-wide chat-page-container">

        <!-- Chat Header -->
        <div class="chat-header">
            <a href="<?php echo $back_link; ?>" class="chat-back-btn" title="Back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="chat-header-info">
                <h3><?php echo htmlspecialchars($other_name); ?></h3>
                <span class="chat-header-job"><?php echo htmlspecialchars($job['title']); ?></span>
            </div>
            <span class="badge badge-in-progress">In Progress</span>
        </div>

        <!-- Messages Area -->
        <div class="chat-messages" id="chatMessages">
            <?php if ($messagesResult->num_rows === 0): ?>
                <div class="chat-empty">
                    <div class="chat-empty-icon">💬</div>
                    <p>No messages yet. Start the conversation!</p>
                </div>
            <?php endif; ?>

            <?php while ($msg = $messagesResult->fetch_assoc()):
                $isSender = ($msg['sender_id'] == $current_user);
                $bubbleClass = $isSender ? 'chat-bubble-sent' : 'chat-bubble-received';
                $senderLabel = $isSender ? 'You' : htmlspecialchars($msg['sender_name'] ?? $msg['sender_email']);
                ?>
                <div class="chat-bubble <?php echo $bubbleClass; ?>">
                    <div class="chat-bubble-name"><?php echo $senderLabel; ?></div>
                    <div class="chat-bubble-content"><?php echo htmlspecialchars($msg['content']); ?></div>
                    <div class="chat-bubble-time">
                        <?php echo date('M j, g:i A', strtotime($msg['timestamp'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Message Input -->
        <form action="send_message.php" method="POST" class="chat-form">
            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
            <div class="chat-input-wrapper">
                <textarea name="content" class="chat-input" placeholder="Type your message..." rows="1"
                    required></textarea>
                <button type="submit" class="chat-send-btn" title="Send">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" />
                    </svg>
                </button>
            </div>
        </form>

    </div>

    <script>
        // Auto-scroll to bottom on page load
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Auto-resize textarea
        const textarea = document.querySelector('.chat-input');
        if (textarea) {
            textarea.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Submit on Enter (Shift+Enter for new line)
            textarea.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (this.value.trim() !== '') {
                        this.closest('form').submit();
                    }
                }
            });
        }
    </script>

</body>

</html>