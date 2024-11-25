<?php
session_start();
include 'dbconnect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $user_id = $_SESSION['user_id'];

    // Delete the booking
    $sql = "DELETE FROM bookings WHERE id = '$booking_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        echo "<p>Ticket canceled successfully!</p>";
    } else {
        echo "<p>Error canceling ticket: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>No booking selected to cancel.</p>";
}

echo '<a href="user_dashboard.php">Go Back to Dashboard</a>';
?>
