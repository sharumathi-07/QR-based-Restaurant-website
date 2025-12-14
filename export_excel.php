<?php
session_start();
if (!isset($_SESSION['manager_id'])) {
    header("Location: manager_login.php");
    exit();
}

include('config.php');

$filter = $_POST['filter'] ?? 'today';
$startDate = $endDate = date('Y-m-d');
if ($filter == 'week') $startDate = date('Y-m-d', strtotime('monday this week'));
elseif ($filter == 'month') $startDate = date('Y-m-01');

$sql = "SELECT DATE(order_date) as day,
            SUM(oi.price * oi.quantity) as revenue,
            SUM(oi.cost_price * oi.quantity) as cost,
            SUM((oi.price - oi.cost_price) * oi.quantity) as profit_loss
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.payment_status='Paid' AND DATE(order_date) BETWEEN '$startDate' AND '$endDate'
        GROUP BY DATE(order_date)
        ORDER BY DATE(order_date)";
$result = $conn->query($sql);

if(!$result) {
    die("Query Failed: " . $conn->error);
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=profit_loss_".date('Y-m-d').".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";

echo "<table border='1'>";
echo "<tr style='background-color:#222;color:#fff;'>
        <th>Date</th>
        <th>Revenue (₹)</th>
        <th>Cost (₹)</th>
        <th>Profit (₹)</th>
        <th>Loss (₹)</th>
      </tr>";

while($row = $result->fetch_assoc()) {
    $profit = $row['profit_loss'] > 0 ? $row['profit_loss'] : 0;
    $loss = $row['profit_loss'] < 0 ? abs($row['profit_loss']) : 0;

    echo "<tr>
            <td>".date('d-m-Y', strtotime($row['day']))."</td>
            <td>₹{$row['revenue']}</td>
            <td>₹{$row['cost']}</td>
            <td>₹$profit</td>
            <td>₹$loss</td>
          </tr>";
}

echo "</table>";
exit;
?>