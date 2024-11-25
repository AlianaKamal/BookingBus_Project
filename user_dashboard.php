<?php
session_start();
include("dbconnect.php");

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch available buses (including price_per_seat)
$buses_query = "SELECT * FROM buses WHERE seats_available > 0";
$buses_result = mysqli_query($conn, $buses_query);

// Fetch user's booking history
$user_query = "SELECT id FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_id = mysqli_fetch_assoc($user_result)['id'];

// Fetch booking history
$bookings_query = "SELECT bookings.id AS booking_id, 
                          buses.bus_name, 
                          buses.departure, 
                          buses.destination, 
                          buses.departure_date,
                          buses.departure_time, 
                          buses.arrival_time, 
                          bookings.seats_booked, 
                          bookings.payment_status, 
                          bookings.booking_time, 
                          (buses.price_per_seat * bookings.seats_booked) AS amount 
                   FROM bookings 
                   JOIN buses ON bookings.bus_id = buses.id 
                   WHERE bookings.user_id = ? 
                   ORDER BY bookings.booking_time DESC";
$stmt = mysqli_prepare($conn, $bookings_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$bookings_result = mysqli_stmt_get_result($stmt);

// Cancel a booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $cancel_query = "SELECT bus_id, seats_booked FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $cancel_query);
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $cancel_result = mysqli_stmt_get_result($stmt);

    if ($cancel_row = mysqli_fetch_assoc($cancel_result)) {
        $bus_id = $cancel_row['bus_id'];
        $seats_to_release = $cancel_row['seats_booked'];

        // Delete the booking
        $delete_booking_query = "DELETE FROM bookings WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_booking_query);
        mysqli_stmt_bind_param($stmt, 'i', $booking_id);
        mysqli_stmt_execute($stmt);

        // Update bus seats
        $update_seats_query = "UPDATE buses SET seats_available = seats_available + ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_seats_query);
        mysqli_stmt_bind_param($stmt, 'ii', $seats_to_release, $bus_id);
        mysqli_stmt_execute($stmt);

        $success = "Booking cancelled successfully.";
    } else {
        $error = "Failed to cancel booking.";
    }
}

// Book a bus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_bus'])) {
    $bus_id = intval($_POST['bus_id']);
    $seats_booked = intval($_POST['seats_booked']);

    // Validate available seats
    $bus_query = "SELECT seats_available, price_per_seat FROM buses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $bus_query);
    mysqli_stmt_bind_param($stmt, 'i', $bus_id);
    mysqli_stmt_execute($stmt);
    $bus_result = mysqli_stmt_get_result($stmt);
    $bus = mysqli_fetch_assoc($bus_result);

    if ($bus && $seats_booked > 0 && $seats_booked <= $bus['seats_available']) {
        // Update bus seats
        $update_seats_query = "UPDATE buses SET seats_available = seats_available - ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_seats_query);
        mysqli_stmt_bind_param($stmt, 'ii', $seats_booked, $bus_id);
        mysqli_stmt_execute($stmt);

        // Insert booking record
        $insert_booking_query = "INSERT INTO bookings (user_id, bus_id, seats_booked, payment_status, booking_time) 
                                 VALUES (?, ?, ?, 'Pending', NOW())";
        $stmt = mysqli_prepare($conn, $insert_booking_query);
        mysqli_stmt_bind_param($stmt, 'iii', $user_id, $bus_id, $seats_booked);
        mysqli_stmt_execute($stmt);

        // Redirect to payment gateway
        $amount = $bus['price_per_seat'] * $seats_booked;
        $booking_id = mysqli_insert_id($conn);
        header("Location: payment_gateaway.php?booking_id=$booking_id&amount=$amount");
        exit();
    } else {
        $error = "Invalid seat selection or insufficient seats available.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body { font-family: 'Poppins', sans-serif; padding: 20px; background: #f4f4f4; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        form { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        input, button { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #4caf50; color: white; padding: 10px; border: none; cursor: pointer; }
        .btn:hover { background: #45a049; }
        a { text-decoration: none; color: white; padding: 10px 20px; background: #4caf50; border-radius: 4px; margin: 10px; }
        a:hover { background: #45a049; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
    </style>
    <script>
        function confirmLogout(event) {
            if (!confirm("Are you sure you want to log out?")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <a href="logout.php" onclick="confirmLogout(event)">Logout</a>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
    <?php if (isset($error)) { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if (isset($success)) { ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <h2>Available Buses</h2>
    <table>
        <thead>
            <tr>
                <th>Bus Name</th>
                <th>Departure</th>
                <th>Destination</th>
                <th>Departure Date & Time</th> <!-- Updated Column -->
                <th>Arrival Time</th>
                <th>Seats Available</th>
                <th>Price per Seat</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bus = mysqli_fetch_assoc($buses_result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($bus['bus_name']); ?></td>
                    <td><?php echo htmlspecialchars($bus['departure']); ?></td>
                    <td><?php echo htmlspecialchars($bus['destination']); ?></td>
                    <td><?php echo htmlspecialchars($bus['departure_date'] . ' ' . $bus['departure_time']); ?></td> <!-- Display Departure Date and Time -->
                    <td><?php echo htmlspecialchars($bus['arrival_time']); ?></td>
                    <td><?php echo htmlspecialchars($bus['seats_available']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($bus['price_per_seat'], 2)); ?> RM</td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                            <input type="number" name="seats_booked" placeholder="Number of Seats" min="1" max="<?php echo $bus['seats_available']; ?>" required>
                            <button type="submit" name="book_bus" class="btn">Book Now</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h2>Your Booking History</h2>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Bus Name</th>
                <th>Departure</th>
                <th>Destination</th>
                <th>Departure Date & Time</th> <!-- Updated Column -->
                <th>Seats Booked</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Cancel</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = mysqli_fetch_assoc($bookings_result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($booking['bus_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['departure']); ?></td>
                    <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                    <td><?php echo htmlspecialchars($booking['departure_date'] . ' ' . $booking['departure_time']); ?></td> <!-- Departure Date and Time -->
                    <td><?php echo htmlspecialchars($booking['seats_booked']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($booking['amount'], 2)); ?> RM</td>
                    <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                    <td>
                        <form action="" method="post" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                            <button type="submit" name="cancel_booking" class="btn">Cancel Booking</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>



