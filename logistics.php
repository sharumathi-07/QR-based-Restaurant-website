<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "restaurant_db");


$sql_revenue = "SELECT SUM(price * quantity) AS total_revenue FROM order_items WHERE status='Completed'";
$result_revenue = $conn->query($sql_revenue);
$row_revenue = $result_revenue->fetch_assoc();
$total_revenue = $row_revenue['total_revenue'] ?? 0;

// Total orders
$sql_orders = "SELECT COUNT(DISTINCT order_id) AS total_orders FROM order_items";
$total_orders = $conn->query($sql_orders)->fetch_assoc()['total_orders'];

// Completed orders
$sql_completed = "SELECT COUNT(DISTINCT order_id) AS completed_orders FROM order_items WHERE status='Completed'";
$completed_orders = $conn->query($sql_completed)->fetch_assoc()['completed_orders'];

// Pending + Preparing orders
$sql_pending = "SELECT COUNT(DISTINCT order_id) AS active_orders FROM order_items WHERE status IN ('Pending','Preparing')";
$active_orders = $conn->query($sql_pending)->fetch_assoc()['active_orders'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logistics & Revenue</title>
    <link rel="icon" type="image/jpg" href="img/icon.jpg"/>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('img/logistics-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: rgba(255,255,255,0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        h2 { text-align: center; }
        .stat {
            font-size: 18px;
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fa;
            border-left: 5px solid #007bff;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
        }
        a:hover { background: #0056b3; }
        canvas {
            margin-top: 25px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h2>📊 Logistics & Revenue</h2>
        <div class="stat">💰 Total Revenue: <b>₹<?= number_format($total_revenue, 2) ?></b></div>
        <div class="stat">📦 Total Orders: <b><?= $total_orders ?></b></div>
        <div class="stat">✅ Completed Orders: <b><?= $completed_orders ?></b></div>
        <div class="stat">⏳ Active Orders (Pending/Preparing): <b><?= $active_orders ?></b></div>

        
        <div style="width:300px; margin:auto;">
          <canvas id="ordersChart"></canvas>
        </div>



        <div style="text-align: center;">
            <a href="dashboard.php">⬅ Back to Dashboard</a>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Completed Orders', 'Active Orders'],
                datasets: [{
                    label: 'Orders',
                    data: [<?= $completed_orders ?>, <?= $active_orders ?>],
                    backgroundColor: ['#28a745', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
