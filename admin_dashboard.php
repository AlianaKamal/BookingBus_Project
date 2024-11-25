<?php
session_start();
include("dbconnect.php");

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch buses (including departure_date)
$buses_query = "SELECT * FROM buses";
$buses_result = mysqli_query($conn, $buses_query);

// Add new bus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_bus'])) {
    $bus_name = mysqli_real_escape_string($conn, $_POST['bus_name']);
    $departure = mysqli_real_escape_string($conn, $_POST['departure']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $departure_date = mysqli_real_escape_string($conn, $_POST['departure_date']);  // Get departure_date from form
    $departure_time = mysqli_real_escape_string($conn, $_POST['departure_time']);
    $arrival_time = mysqli_real_escape_string($conn, $_POST['arrival_time']);
    $seats_available = mysqli_real_escape_string($conn, $_POST['seats_available']);
    $price_per_seat = mysqli_real_escape_string($conn, $_POST['price_per_seat']);

    $insert_query = "INSERT INTO buses (bus_name, departure, destination, departure_date, departure_time, arrival_time, seats_available, price_per_seat) 
                     VALUES ('$bus_name', '$departure', '$destination', '$departure_date', '$departure_time', '$arrival_time', $seats_available, $price_per_seat)";
    mysqli_query($conn, $insert_query);
    $_SESSION['success'] = "Bus added successfully.";
    header("Location: admin_dashboard.php");
    exit();
}

// Delete bus schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_bus'])) {
    $bus_id = intval($_POST['bus_id']);

    // Check if the bus is referenced in bookings
    $check_query = "SELECT COUNT(*) AS booking_count FROM bookings WHERE bus_id = $bus_id";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);

    if ($check_row['booking_count'] > 0) {
        $_SESSION['error'] = "Cannot delete the bus as it is referenced in bookings.";
    } else {
        $delete_query = "DELETE FROM buses WHERE id = $bus_id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success'] = "Bus schedule deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete the bus.";
        }
    }
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch all users' booking history
$bookings_query = "SELECT bookings.id AS booking_id, users.username, buses.bus_name, buses.departure, buses.destination, bookings.seats_booked, bookings.payment_status, bookings.booking_time, buses.departure_date 
                   FROM bookings 
                   JOIN users ON bookings.user_id = users.id 
                   JOIN buses ON bookings.bus_id = buses.id 
                   ORDER BY bookings.booking_time DESC";
$bookings_result = mysqli_query($conn, $bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Same CSS as before */
        body { font-family: 'Poppins', sans-serif; padding: 20px; background: #f4f4f4; }
        h1 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        form { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        input, button { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #4caf50; color: white; padding: 10px; border: none; cursor: pointer; }
        .btn:hover { background: #45a049; }
        .delete-btn { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .delete-btn:hover { background: #c0392b; }
        a { text-decoration: none; color: white; padding: 10px 20px; background: #4caf50; border-radius: 4px; margin: 10px; }
        a:hover { background: #45a049; }
    </style>
</head>
<body>
    <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
    <h1>Administration Management Online Booking Bus</h1>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green; text-align: center;'>{$_SESSION['success']}</p>";
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo "<p style='color: red; text-align: center;'>{$_SESSION['error']}</p>";
        unset($_SESSION['error']);
    }
    ?>

    <h2>Bus Schedules</h2>
    <table>
        <thead>
            <tr>
                <th>Bus Name</th>
                <th>Departure</th>
                <th>Destination</th>
                <th>Departure Date</th> <!-- Added Departure Date column -->
                <th>Departure Time</th>
                <th>Arrival Time</th>
                <th>Seats Available</th>
                <th>Price Per Seat</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bus = mysqli_fetch_assoc($buses_result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($bus['bus_name']); ?></td>
                    <td><?php echo htmlspecialchars($bus['departure']); ?></td>
                    <td><?php echo htmlspecialchars($bus['destination']); ?></td>
                    <td><?php echo htmlspecialchars($bus['departure_date']); ?></td> <!-- Display Departure Date -->
                    <td><?php echo htmlspecialchars($bus['departure_time']); ?></td>
                    <td><?php echo htmlspecialchars($bus['arrival_time']); ?></td>
                    <td><?php echo htmlspecialchars($bus['seats_available']); ?></td>
                    <td><?php echo htmlspecialchars($bus['price_per_seat']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                            <button type="submit" name="delete_bus" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <form method="POST" action="">
        <h2>Add New Bus</h2>
        <input type="text" name="bus_name" placeholder="Bus Name" required>
        <input type="text" name="departure" placeholder="Departure" required>
        <input type="text" name="destination" placeholder="Destination" required>
        <input type="date" name="departure_date" required> <!-- Added departure_date input -->
        <input type="time" name="departure_time" required>
        <input type="time" name="arrival_time" required>
        <input type="number" name="seats_available" placeholder="Seats Available" required>
        <input type="number" name="price_per_seat" placeholder="Price Per Seat" required>
        <button type="submit" name="add_bus" class="btn">Add Bus</button>
    </form>

    <h2>User's Booking</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Bus Name</th>
                <th>Departure</th>
                <th>Destination</th>
                <th>Seats Booked</th>
                <th>Payment Status</th>
                <th>Booking Time</th>
                <th>Departure Date</th> <!-- Added Departure Date -->
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = mysqli_fetch_assoc($bookings_result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['username']); ?></td>
                    <td><?php echo htmlspecialchars($booking['bus_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['departure']); ?></td>
                    <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                    <td><?php echo htmlspecialchars($booking['seats_booked']); ?></td>
                    <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                    <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                    <td><?php echo htmlspecialchars($booking['departure_date']); ?></td> <!-- Display Departure Date -->
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>




