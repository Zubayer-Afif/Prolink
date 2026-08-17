<?php
session_start();
include __DIR__ . '/../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['type'] = $user['type'];
        $_SESSION['user_name'] = $user['name'] ?? $user['email'];

        if ($user['type'] == 'client') {
            header("Location: ../client/dashboard.php");
            exit();
        } else {
            header("Location: ../freelancer/dashboard.php");
            exit();
        }

    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="container">
        <h1>Welcome Back</h1>
        <p style="margin-bottom: 16px;">Login to ProLink</p>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" class="btn login" style="width:100%; margin-top:12px;">Login</button>
        </form>

        <?php if (!empty($error)): ?>
            <div class="msg-error" style="margin-top: 12px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <p style="margin-top: 16px;">
            <a href="register.php" class="link-subtle">Don't have an account? Register</a>
        </p>
    </div>

</body>

</html>