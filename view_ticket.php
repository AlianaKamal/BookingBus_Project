<?php
session_start();
include('dbconnect.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your ticket.");
}

// Ensure the booking ID is provided
if (!isset($_GET['booking_id'])) {
    die("Invalid ticket request.");
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION['user_id'];

// Fetch ticket details
$sql = "SELECT 
            b.id AS booking_id,
            u.username AS user_name,
            u.email AS user_email,
            bus.bus_name,
            bus.departure,
            bus.destination,
            bus.departure_time,
            bus.arrival_time,
            b.seats_booked,
            b.payment_status
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN buses bus ON b.bus_id = bus.id
        WHERE b.id = $booking_id AND b.user_id = $user_id AND b.payment_status = 'Paid'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    die("Invalid ticket or you are not authorized to view this ticket.");
}

$ticket = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .ticket-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .ticket-container h1 {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .ticket-details {
            text-align: left;
            font-size: 1rem;
            margin-bottom: 20px;
        }
        .ticket-details p {
            margin: 5px 0;
        }
        button, a {
            display: inline-block;
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin: 5px;
            font-size: 1rem;
        }
        button:hover, a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <h1>Your Ticket</h1>
        <div class="ticket-details">
            <p><strong>Booking ID:</strong> <?php echo $ticket['booking_id']; ?></p>
            <p><strong>Name:</strong> <?php echo $ticket['user_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $ticket['user_email']; ?></p>
            <p><strong>Bus Name:</strong> <?php echo $ticket['bus_name']; ?></p>
            <p><strong>From:</strong> <?php echo $ticket['departure']; ?></p>
            <p><strong>To:</strong> <?php echo $ticket['destination']; ?></p>
            <p><strong>Departure Time:</strong> <?php echo $ticket['departure_time']; ?></p>
            <p><strong>Arrival Time:</strong> <?php echo $ticket['arrival_time']; ?></p>
            <p><strong>Seats Booked:</strong> <?php echo $ticket['seats_booked']; ?></p>
            <p><strong>Payment Status:</strong> <?php echo $ticket['payment_status']; ?></p>
        </div>
        <button onclick="window.print()">Print Ticket</button>
        <a href="user_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

