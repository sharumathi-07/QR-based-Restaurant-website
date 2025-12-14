<?php
session_start();
if (!isset($_SESSION['waiter'])) {
    header("Location: waiter_login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "restaurant_db");

// Fetch order details
$sql = "SELECT order_id, item_name, status FROM order_items ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Waiter Dashboard</title>
    <link rel="icon" type="image/jpg" href="img/icon.jpg"/>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: url('img/waiter-bg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .logout { float: right; margin-bottom: 15px; }
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover { background: #f1f1f1; }
        h2 { text-align: center; }
    </style>
    <script>
        // Auto-refresh every 10 sec
        setTimeout(function() {
            window.location.reload();
        }, 10000);
    </script>
</head>
<body>
    <a href="logout.php" class="logout">Logout</a>
    <h2>🍽️ Waiter Dashboard</h2>
    <table>
        <tr>
            <th>Order Number</th>
            <th>Item</th>
            <th>Status</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['order_id'] ?></td>
            <td><?= $row['item_name'] ?></td>
            <td><?= $row['status'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

 