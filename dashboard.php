<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "restaurant_db");

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $conn->query("UPDATE order_items SET status='$new_status' WHERE order_id=$order_id");
}

// Fetch orders and table numbers
$sql = "SELECT oi.*, o.table_number 
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        ORDER BY oi.created_at DESC";

$result = $conn->query($sql);
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['order_id']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kitchen Dashboard</title>
    <link rel="icon" type="image/jpg" href="img/icon.jpg"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            background: linear-gradient(135deg, #0a0a0a, #1a1a1a);
            color: #fff;
            padding: 20px;
            min-height: 100vh;
        }

        h2 {
            text-align: center;
            color: #ffcc00;
            margin-bottom: 25px;
        }

        .logout {
            float: right;
            color: #ff4d4d;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .logout:hover {
            background: #ff4d4d;
            color: white;
        }

        .orders-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            justify-content: center;
        }

      
        .order-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

        .order-header {
            font-weight: 600;
            color: #ffcc00;
            text-align: center;
            margin-bottom: 10px;
        }

        .order-header small {
            color: #ccc;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }

        th, td {
            padding: 8px;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
            border-radius: 4px;
        }

        td {
            color: #ddd;
        }

        .status-box {
            margin-top: 15px;
            text-align: center;
        }

        .status-box span {
            display: inline-block;
            margin-bottom: 8px;
        }

        .status-Pending {
            background-color: #ffc107;
            color: #000;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .status-Preparing {
            background-color: #fd7e14;
            color: #fff;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .status-Completed {
            background-color: #28a745;
            color: #fff;
            padding: 4px 10px;
            border-radius: 6px;
        }

        select {
            padding: 8px;
            border-radius: 8px;
            border: none;
            outline: none;
            font-weight: 600;
        }

        button {
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            margin-top: 10px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: bold;
        }

        button:hover {
            background: #218838;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            h2 {
                font-size: 20px;
            }
            .order-card {
                width: 100%;
            }
            table th, table td {
                font-size: 12px;
                padding: 6px;
            }
        }

        @media (max-width: 480px) {
            .logout {
                float: none;
                display: block;
                text-align: center;
                margin-bottom: 15px;
            }
        }
    </style>

    <script>
        // Auto-refresh every 10 seconds
        setTimeout(() => location.reload(), 10000);
    </script>
</head>
<body>
    <a href="logout.php" class="logout">Logout</a>
    <h2>🍳 Kitchen Dashboard</h2>

    <div class="orders-container">
        <?php foreach ($orders as $order_id => $items): ?>
            <div class="order-card">
                <div class="order-header">
                    Order #<?= $order_id ?> | Table <?= $items[0]['table_number'] ?><br>
                    <small>Ordered At: <?= $items[0]['created_at'] ?></small>
                </div>

                <table>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['item_name'] ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= $item['price'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <div class="status-box">
                    <div>
                        Current Status: 
                        <span class="status-<?= $items[0]['status'] ?>"><?= $items[0]['status'] ?></span>
                    </div>

                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        <select name="status">
                            <option value="Pending" <?= ($items[0]['status']=='Pending')?'selected':'' ?>>Pending</option>
                            <option value="Preparing" <?= ($items[0]['status']=='Preparing')?'selected':'' ?>>Preparing</option>
                            <option value="Completed" <?= ($items[0]['status']=='Completed')?'selected':'' ?>>Completed</option>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>