<?php
session_start();
include('dbconnect.php');

// Ensure the required booking ID is provided
if (!isset($_POST['booking_id'])) {
    die("<p style='color: red;'>Invalid payment request.</p>");
}

$booking_id = intval($_POST['booking_id']);

// Validate form inputs
$card_number = trim($_POST['card_number']);
$expiry_date = trim($_POST['expiry_date']);
$cvv = intval($_POST['cvv']);

// Validate booking ID exists and payment is pending
$check_sql = "SELECT * FROM bookings WHERE id = $booking_id AND payment_status = 'Pending'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    // Update the booking's payment status to "Paid"
    $update_sql = "UPDATE bookings SET payment_status = 'Paid' WHERE id = $booking_id AND payment_status = 'Pending'";
    if (mysqli_query($conn, $update_sql)) {
        // Redirect to view_ticket.php with booking ID
        header("Location: view_ticket.php?booking_id=$booking_id");
        exit();
    } else {
        echo "<p style='color: red;'>Error updating payment status: " . mysqli_error($conn) . "</p>";
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Failed</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background-color: #f4f4f9;
            }
            .error-container {
                text-align: center;
                background: #ffffff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                max-width: 400px;
            }
            .error-container h1 {
                color: #FF0000;
                font-size: 2rem;
            }
            .error-container p {
                font-size: 1rem;
                margin: 20px 0;
            }
            .error-container a {
                display: inline-block;
                background: #007BFF;
                color: #fff;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                transition: 0.3s;
            }
            .error-container a:hover {
                background: #0056b3;
            }
            .error-container img {
                width: 80px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828665.png" alt="Error">
            <h1>Payment Failed</h1>
            <p>Invalid booking or payment has already been processed.</p>
            <a href="user_dashboard.php">Return to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
}
?>
