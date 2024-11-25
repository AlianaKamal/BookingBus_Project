<?php
session_start();
error_reporting(0);
include("dbconnect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bus Booking</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('bg1.jpg') no-repeat center center/cover;
            color: white;
        }
        .nav {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background: rgba(0, 0, 0, 0.7);
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s, color 0.3s;
        }
        .nav a:hover {
            background: #FFD100;
            color: black;
        }
        .hero {
            text-align: center;
            margin-top: 100px;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 600;
        }
        .hero p {
            font-size: 1.4rem;
            margin: 20px 0;
            line-height: 1.5;
        }
        .hero a {
            display: inline-block;
            margin-top: 20px;
            padding: 15px 40px;
            background: #FFD100;
            color: black;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
            transition: background 0.3s;
        }
        .hero a:hover {
            background: #FFC107;
        }
    </style>
</head>
<body>

    <div class="nav">
        <div><a href="home.php">Home</a></div>
        <div>
            <?php if (!isset($_SESSION['username'])) { ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php } else { ?>
                <a href="logout.php">Login</a>
                <?php if ($_SESSION['role'] == 'admin') { ?>
                    <a href="admin_dashboard.php">Dashboard</a>
                <?php } else { ?>
                    <a href="user_dashboard.php">Dashboard</a>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
    <div class="hero">
        <h1>Welcome to Online Bus Booking</h1>
        <p>Book your tickets with ease and comfort.<br>Safe. Reliable. Affordable.</p>
        <a href="login.php">Get Started</a>
    </div>
</body>
</html>

