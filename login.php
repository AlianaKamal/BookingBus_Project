<?php
session_start();
include("dbconnect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = $_POST['role'];

    if ($role == 'admin') {
        $query = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    } else {
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    }

    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $role;

        if ($role == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: user_dashboard.php');
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: #333;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #764ba2;
        }
        .error {
            color: #ff5252;
            margin-bottom: 15px;
        }
        .back-link {
            position: absolute;
            top: 15px;
            left: 15px;
            text-decoration: none;
            color: #667eea;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .link {
            margin-top: 20px;
            font-size: 14px;
            color: #667eea;
        }
        .link a {
            color: #667eea;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="home.php" class="back-link">← Back to Home</a>
        <h1>Login</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Login as</label>
                <select id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="link">
            <a href="forgot_passwword.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>


