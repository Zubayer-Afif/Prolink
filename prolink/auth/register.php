<?php
include('../config/db.php');

$message = "";
$msgType = "";
$selectedType = $_POST['type'] ?? $_GET['type'] ?? 'client';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $type = $_POST['type'];

    // Type-specific fields
    $hourly_rate = ($type === 'freelancer') ? ($_POST['hourly_rate'] ?? null) : null;
    $bio = ($type === 'freelancer') ? ($_POST['bio'] ?? null) : null;
    $company_name = ($type === 'client') ? ($_POST['company_name'] ?? null) : null;

    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, type, hourly_rate, bio, company_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdss", $name, $email, $password, $type, $hourly_rate, $bio, $company_name);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;

        // Insert skills into freelancer_skills table
        if ($type === 'freelancer' && !empty($_POST['skills'])) {
            $skillsList = array_map('trim', explode(',', $_POST['skills']));
            $skillStmt = $conn->prepare("INSERT INTO freelancer_skills (user_id, skill) VALUES (?, ?)");
            foreach ($skillsList as $skill) {
                if (!empty($skill)) {
                    $skillStmt->bind_param("is", $userId, $skill);
                    $skillStmt->execute();
                }
            }
        }

        $message = "Registered successfully! Please login.";
        $msgType = "success";
    } else {
        $message = "Error: " . $conn->error;
        $msgType = "error";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="container">
        <h1>Create Account</h1>
        <p style="margin-bottom: 16px;">Join ProLink today</p>

        <!-- Type selector -->
        <div style="margin-bottom: 16px;">
            <a href="register.php?type=client"
                class="btn btn-sm <?php echo ($selectedType === 'client') ? 'btn-purple' : 'btn-outline'; ?>">
                Client
            </a>
            <a href="register.php?type=freelancer"
                class="btn btn-sm <?php echo ($selectedType === 'freelancer') ? 'btn-purple' : 'btn-outline'; ?>">
                Freelancer
            </a>
        </div>

        <form method="POST">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($selectedType); ?>">

            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <?php if ($selectedType === 'client'): ?>
                <input type="text" name="company_name" placeholder="Company Name (optional)">
            <?php else: ?>
                <input type="text" name="skills" placeholder="Skills (e.g. PHP, CSS, Design)">
                <input type="number" name="hourly_rate" placeholder="Hourly Rate ($)" step="0.01" min="0">
                <textarea name="bio" placeholder="Short Bio (optional)"></textarea>
            <?php endif; ?>

            <button type="submit" name="register" class="btn post"
                style="width:100%; margin-top:16px;">Register</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="msg-<?php echo $msgType; ?>" style="margin-top: 12px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <p style="margin-top: 16px;">
            <a href="login.php" class="link-subtle">Already have an account? Login</a>
        </p>
    </div>

</body>

</html>