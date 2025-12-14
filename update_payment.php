<?php
$conn = new mysqli("localhost", "root", "", "restaurant_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orderId = '';
$tableNumber = '';
$orderItems = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderId = $_POST['order_id'];
    $method  = $_POST['method'];

    // Get table number
    if (isset($_POST['table_number'])) {
        $tableNumber = $_POST['table_number'];
    } else {
        $stmt2 = $conn->prepare("SELECT table_number FROM orders WHERE order_id = ?");
        $stmt2->bind_param("i", $orderId);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($res2 && $row = $res2->fetch_assoc()) {
            $tableNumber = $row['table_number'];
        }
        $stmt2->close();
    }

    // Update payment method & status
    $stmt = $conn->prepare("UPDATE orders SET payment_method=?, payment_status='Paid' WHERE order_id=?");
    $stmt->bind_param("si", $method, $orderId);
    $stmt->execute();
    $stmt->close();
}

// Fetch order items
if ($orderId) {
    $sql = "SELECT oi.*, o.table_number 
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.order_id = $orderId";
    $result = $conn->query($sql);

    while ($item = $result->fetch_assoc()) {
        $orderItems[] = $item;
    }
}

// Determine overall status
$overallStatus = '';
if (!empty($orderItems)) {
    $statuses = array_column($orderItems, 'status');
    if (in_array('Pending', $statuses)) {
        $overallStatus = 'Pending';
    } elseif (in_array('Preparing', $statuses)) {
        $overallStatus = 'Preparing';
    } elseif (!in_array('Pending', $statuses) && !in_array('Preparing', $statuses)) {
        $overallStatus = 'Completed';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Status</title>
    <link rel="icon" type="image/jpg" href="img/icon.jpg"/>
    <meta charset="UTF-8">

    <!-- ✅ Auto-refresh every 5 seconds -->
    <script>
        setTimeout(() => {
            // Automatically reload the page with same data via POST using fetch()
            const formData = new FormData();
            formData.append('order_id', '<?= $orderId ?>');
            formData.append('method', '<?= $method ?? '' ?>');
            formData.append('table_number', '<?= $tableNumber ?>');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.open();
                document.write(html);
                document.close();
            });
        }, 5000); // refresh every 5 seconds
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #000000, #0a0a0a, #1a1a1a);
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .container {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 25px rgba(0, 0, 0, 0.5);
            max-width: 700px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.7s ease-in-out;
        }

        h2 {
            color: #00ffcc;
            margin-bottom: 10px;
        }

        p {
            color: #ccc;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background: #00ffcc;
            color: #000;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        .status-Pending {
            background: #ffc107;
            color: #000;
            padding: 4px 10px;
            border-radius: 5px;
        }

        .status-Preparing {
            background: #fd7e14;
            color: #fff;
            padding: 4px 10px;
            border-radius: 5px;
        }

        .status-Completed {
            background: #28a745;
            color: #fff;
            padding: 4px 10px;
            border-radius: 5px;
        }

        .message {
            font-size: 18px;
            margin-top: 20px;
            color: #fff;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 20px;
            }
            th, td { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✅ Payment Successful via <?= htmlspecialchars($method ?? '') ?></h2>
        <p>Thank you for your order!</p>

        <?php if ($tableNumber !== ''): ?>
            <p>Order saved for Table <strong><?= htmlspecialchars($tableNumber) ?></strong></p>
            <p>Order number: <strong><?= htmlspecialchars($orderId) ?></strong></p>
        <?php endif; ?>

        <?php if ($overallStatus == 'Pending'): ?>
            <div class="message">⌛ Your order is pending. The kitchen will start preparing soon.</div>
        <?php elseif ($overallStatus == 'Preparing'): ?>
            <div class="message">👨‍🍳 Your order is being prepared. Please wait.</div>
        <?php elseif ($overallStatus == 'Completed'): ?>
            <div class="message">✅ Your order is ready! Enjoy your meal.</div>
        <?php endif; ?>

        <?php if (!empty($orderItems)): ?>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td>₹<?= htmlspecialchars($item['price']) ?></td>
                        <td><span class="status-<?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>