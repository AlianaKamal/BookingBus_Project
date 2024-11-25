<?php
session_start();

if (!isset($_GET['booking_id']) || !isset($_GET['amount'])) {
    die("Invalid payment request.");
}

$booking_id = intval($_GET['booking_id']);
$amount = floatval($_GET['amount']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        .container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #333;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Gateway</h1>
        <p>Amount to pay: <strong>$<?php echo number_format($amount, 2); ?></strong></p>
        <form method="POST" action="payment_process.php">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <label for="card_number">Card Number</label>
            <input type="text" id="card_number" name="card_number" placeholder="Enter card number" required>
            
            <label for="expiry_date">Expiry Date</label>
            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
            
            <label for="cvv">CVV</label>
            <input type="number" id="cvv" name="cvv" placeholder="Enter CVV" required>
            
            <button type="submit">Pay Now</button>
        </form>
    </div>
</body>
</html>
