<?php
$conn = new mysqli("localhost", "root", "", "restaurant_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderData = json_decode($_POST['orderData'], true);

    $tableNumber = $orderData['table'];
    $items = $orderData['items'];

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (table_number) VALUES (?)");
    $stmt->bind_param("s", $tableNumber);
    $stmt->execute();
    $orderId = $stmt->insert_id;

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt->bind_param("isid", $orderId, $item['name'], $item['qty'], $item['price']);
        $stmt->execute();
    }

    // Calculate total
    $totalAmount = 0;
    foreach ($items as $item) {
        $totalAmount += $item['price'] * $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment</title>
<link rel="icon" type="image/jpg" href="img/icon.jpg"/>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #000000ff;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Payment Container */
.payment-container {
    background: #cfccccff;
    width: 100%;
    max-width: 450px;
    border-radius: 15px;
    box-shadow: 0px 15px 40px rgba(0,0,0,0.2);
    padding: 30px;
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
}

/* Header */
.payment-container h2 {
    margin-bottom: 20px;
    color: #ff5722;
    font-size: 24px;
}

/* Total */
.total {
    font-size: 20px;
    font-weight: bold;
    color: #28a745;
    margin-bottom: 25px;
}

/* Buttons */
.payment-btn {
    width: 100%;
    padding: 14px 0;
    margin: 10px 0;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
}

.cash-btn { background: #28a745; color: #fff; }
.card-btn { background: #007bff; color: #fff; }
.upi-btn { background: #ff9800; color: #fff; }

.payment-btn:hover {
    opacity: 0.85;
}

/* Card/UPI Form */
#paymentForm {
    display: none;
    margin-top: 20px;
    text-align: left;
    animation: slideDown 0.4s ease-in-out;
}

#paymentForm label {
    font-size: 14px;
    color: #333;
    font-weight: bold;
}

#paymentForm input {
    width: 100%;
    padding: 10px;
    margin: 8px 0 15px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
}

#paymentForm .submitBtn {
    width: 100%;
    background: #007bff;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
}

#paymentForm .submitBtn:hover {
    background: #0056b3;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 480px) {
    .payment-container { padding: 20px; }
    .payment-btn { font-size: 15px; padding: 12px 0; }
}
</style>
</head>
<body>

<div class="payment-container">
    <h2>💳 Select Payment Method</h2>
    <div class="total">Total: ₹ <?= $totalAmount ?? '0' ?></div>

    <form method="POST" action="update_payment.php">
        <input type="hidden" name="order_id" value="<?= $orderId ?>">
        <button type="submit" name="method" value="Cash" class="payment-btn cash-btn">Pay with Cash</button>
        <button type="button" onclick="showPaymentForm('Card')" class="payment-btn card-btn">Pay with Card</button>
        <button type="button" onclick="showPaymentForm('UPI')" class="payment-btn upi-btn">UPI/QR Code</button>
    </form>

    <form id="paymentForm" method="POST" action="update_payment.php">
        <input type="hidden" name="order_id" value="<?= $orderId ?>">
        <input type="hidden" name="method" id="paymentMethod" value="Card">

        <label>Card / UPI Number</label>
        <input type="text" name="card_number" placeholder="Enter Card / UPI ID" required>

        <label>CVV / PIN</label>
        <input type="password" name="cvv" placeholder="Enter CVV / PIN" required>

        <button type="submit" class="submitBtn">Pay Now</button>
    </form>
</div>

<script>
function showPaymentForm(method) {
    document.getElementById("paymentForm").style.display = "block";
    document.getElementById("paymentMethod").value = method;
}
</script>

</body>
</html>


