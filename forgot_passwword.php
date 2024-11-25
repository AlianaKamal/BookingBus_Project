<?php
session_start();
require_once "dbconnect.php";

$message = '';
$message_type = ''; // Used to differentiate between success and error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_request'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if the email exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Generate a temporary password
        $temp_password = bin2hex(random_bytes(4)); // Random 8-character password
        $hashed_password = md5($temp_password);

        // Update the password in the database
        $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
        if (mysqli_query($conn, $sql)) {
            $message = "Your password has been reset successfully! Temporary password: <b>$temp_password</b><br>Please log in and change your password immediately.";
            $message_type = 'success';
        } else {
            $message = "An error occurred while resetting your password: " . mysqli_error($conn);
            $message_type = 'error';
        }
    } else {
        $message = "We could not find an account with that email address.";
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .form-container h2 {
            margin-bottom: 20px;
            color: #555;
        }
        .form-container .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-container .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .form-container .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-container label {
            display: block;
            margin-bottom: 10px;
            text-align: left;
            color: #555;
            font-size: 14px;
        }
        .form-container input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-container button {
            background: #6e8efb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background: #5a78e3;
        }
        .form-container a {
            color: #6e8efb;
            text-decoration: none;
            font-size: 14px;
        }
        .form-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Forgot Password</h2>
        <?php if (!empty($message)): ?>
            <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Enter your email address:</label>
            <input type="email" name="email" id="email" placeholder="e.g., user@example.com" required>
            <button type="submit" name="reset_request">Reset Password</button>
        </form>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>

