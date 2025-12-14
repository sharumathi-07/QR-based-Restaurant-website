<?php
session_start();
if (!isset($_SESSION['manager_id'])) {
    header("Location: manager_login.php");
    exit();
}

include('config.php');

$filter = $_GET['filter'] ?? 'today';
$startDate = $endDate = date('Y-m-d');
if ($filter=='week') $startDate = date('Y-m-d', strtotime('monday this week'));
elseif ($filter=='month') $startDate = date('Y-m-01');

function fetchAssoc($conn, $sql) {
    $res = $conn->query($sql);
    if(!$res) die("Query Failed: ".$conn->error);
    return $res->fetch_all(MYSQLI_ASSOC);
}

$startDateTime = $startDate . ' 00:00:00';
$endDateTime   = $endDate . ' 23:59:59';

// Total revenue
$sql = "SELECT SUM(price*quantity) as total 
        FROM order_items oi 
        JOIN orders o ON o.order_id = oi.order_id 
        WHERE o.payment_status='Paid' 
        AND o.order_date BETWEEN '$startDateTime' AND '$endDateTime'";
$result = $conn->query($sql);
$totalRevenue = $result ? $result->fetch_assoc()['total'] ?? 0 : 0;

// Total paid orders
$sql = "SELECT COUNT(*) as totalOrders 
        FROM orders 
        WHERE payment_status='Paid' 
        AND order_date BETWEEN '$startDateTime' AND '$endDateTime'";
$result = $conn->query($sql);
$totalOrders = $result ? $result->fetch_assoc()['totalOrders'] ?? 0 : 0;

// Pending items
$sql = "SELECT COUNT(*) as pendingItems FROM order_items WHERE status='Pending'";
$result = $conn->query($sql);
$pendingItems = $result ? $result->fetch_assoc()['pendingItems'] ?? 0 : 0;

// Paid orders (same as totalOrders, optional)
$sql = "SELECT COUNT(*) as paidOrders 
        FROM orders 
        WHERE payment_status='Paid' 
        AND order_date BETWEEN '$startDateTime' AND '$endDateTime'";
$result = $conn->query($sql);
$paidOrders = $result ? $result->fetch_assoc()['paidOrders'] ?? 0 : 0;

// Revenue per day
$dayRevenueData = fetchAssoc($conn,"SELECT DATE(order_date) as day, SUM(oi.price*oi.quantity) as total 
    FROM orders o JOIN order_items oi ON o.order_id=oi.order_id
    WHERE o.payment_status='Paid' AND DATE(order_date) BETWEEN '$startDate' AND '$endDate'
    GROUP BY DATE(order_date) ORDER BY DATE(order_date)");

// Revenue per order
$orderRevenueData = fetchAssoc($conn,"SELECT o.order_id, SUM(oi.price*oi.quantity) as total 
    FROM orders o JOIN order_items oi ON o.order_id=oi.order_id
    WHERE o.payment_status='Paid' AND DATE(order_date) BETWEEN '$startDate' AND '$endDate'
    GROUP BY o.order_id ORDER BY o.order_id ASC");

// Profit & Loss per day
$dayData = fetchAssoc($conn,"SELECT DATE(order_date) as day,
    SUM(oi.price*oi.quantity) as revenue,
    SUM(oi.cost_price*oi.quantity) as cost,
    SUM(CASE WHEN (oi.price-oi.cost_price)*oi.quantity>0 THEN (oi.price-oi.cost_price)*oi.quantity ELSE 0 END) as profit,
    SUM(CASE WHEN (oi.price-oi.cost_price)*oi.quantity<0 THEN (oi.price-oi.cost_price)*oi.quantity ELSE 0 END) as loss
    FROM orders o JOIN order_items oi ON o.order_id=oi.order_id
    WHERE o.payment_status='Paid' AND DATE(order_date) BETWEEN '$startDate' AND '$endDate'
    GROUP BY DATE(order_date) ORDER BY DATE(order_date)");

// Payment breakdown
$paymentData = fetchAssoc($conn,"SELECT payment_method, SUM(oi.price*oi.quantity) as total 
    FROM orders o JOIN order_items oi ON o.order_id=oi.order_id
    WHERE o.payment_status='Paid' AND DATE(order_date) BETWEEN '$startDate' AND '$endDate'
    GROUP BY payment_method");

// Top 5 dishes
$topItems = fetchAssoc($conn,"SELECT item_name, SUM(quantity) as total_qty
    FROM order_items oi JOIN orders o ON o.order_id=oi.order_id
    WHERE o.payment_status='Paid' AND DATE(order_date) BETWEEN '$startDate' AND '$endDate'
    GROUP BY item_name ORDER BY total_qty DESC LIMIT 5");

// Pending orders
$pendingOrders = fetchAssoc($conn,"SELECT o.order_id, o.table_number, oi.item_name, oi.status
    FROM orders o JOIN order_items oi ON o.order_id=oi.order_id
    WHERE oi.status='Pending' ORDER BY o.order_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Revenue Dashboard</title>
<link rel="icon" type="image/jpg" href="img/icon.jpg"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family:Poppins,sans-serif; background:#000; color:#fff; }
.card { border:none; border-radius:15px; box-shadow:0 2px 10px rgba(160,154,154,0.5); }
.navbar { background:#1f1f1f; }
.navbar-brand { color:#fff; font-weight:600; }
.navbar-brand:hover { color:#e2e8f0; }
.chart-container { background:#1c1c1c; border-radius:15px; padding:15px; margin-top:20px; }
.filters { margin-top:20px; text-align:center; }
.filter-option:hover {
    background-color: #3b82f6;
    color: #fff;
    transform: scale(1.05);
}
.form-select { background:#1c1c1c; color:#fff; border:none; }
.card h5, .card h3 { color:#fff; }
.table-dark tbody tr td { color:#fff; }
@media (max-width:768px){ .chart-container{ width:100%; margin:10px 0; } }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
<div class="container-fluid">
<a class="navbar-brand" href="#">🍽 Revenue Dashboard</a>
<div class="d-flex">
<a href="manager_logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</div>
</div>
</nav>

<div class="container mt-4">
<div class="filters">
<label class="me-2">Filter:</label>
<select id="filterSelect" class="form-select d-inline w-auto">
<option value="today" <?= $filter=='today'?'selected':'' ?>>Today</option>
<option value="week" <?= $filter=='week'?'selected':'' ?>>This Week</option>
<option value="month" <?= $filter=='month'?'selected':'' ?>>This Month</option>
</select>
</div>

<div class="row g-3 mt-3">
<div class="col-6 col-md-3"><div class="card bg-success p-3 text-center"><h5>Total Revenue</h5><h3>₹<?= number_format($totalRevenue,2) ?></h3></div></div>
<div class="col-6 col-md-3"><div class="card bg-primary p-3 text-center"><h5>Total Orders</h5><h3><?= $totalOrders ?></h3></div></div>
<div class="col-6 col-md-3"><div class="card bg-warning p-3 text-center"><h5>Pending Items</h5><h3><?= $pendingItems ?></h3></div></div>
<div class="col-6 col-md-3"><div class="card bg-danger p-3 text-center"><h5>Paid Orders</h5><h3><?= $paidOrders ?></h3></div></div>
</div>

<!-- Revenue Per Day -->
<div class="chart-container">
<h5 class="text-center mb-2">📈 Revenue Per Day</h5>
<canvas id="dayChart" height="100"></canvas>
</div>

<!-- Revenue Per Order -->
<div class="chart-container mt-4">
<h5 class="text-center mb-2">💰 Revenue Per Order</h5>
<canvas id="orderChart" height="100"></canvas>
</div>

<!-- Profit & Loss -->
<div class="chart-container mt-4">
<h5 class="text-center mb-2">📊 Daily Profit & Loss</h5>
<canvas id="profitLossChart" height="100"></canvas>
</div>

<!-- Payment and Top Items -->
<div class="row mt-4">
<div class="col-md-6">
<div class="chart-container">
<h5 class="text-center mb-2">💳 Payment Method Breakdown</h5>
<canvas id="paymentChart" height="100"></canvas>
</div>
</div>
<div class="col-md-6">
<div class="chart-container">
<h5 class="text-center mb-2">🔥 Top 5 Selling Dishes</h5>
<canvas id="topItemsChart" height="100"></canvas>
</div>
</div>
</div>

<div class="chart-container mt-4">
<h5 class="text-center mb-2">📝 Pending Orders</h5>
<table class="table table-dark" id="pendingTable">
<thead>
<tr><th>Order ID</th><th>Table</th><th>Item</th><th>Status</th></tr>
</thead>
<tbody>
<?php foreach($pendingOrders as $order): ?>
<tr>
<td><?= $order['order_id'] ?></td>
<td><?= $order['table_number'] ?></td>
<td><?= $order['item_name'] ?></td>
<td class="<?= $order['status']=='Pending'?'text-warning':'text-success' ?>"><?= $order['status'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="text-center mt-3">
<form method="POST" action="export_excel.php">
<input type="hidden" name="filter" value="<?= $filter ?>">
<button type="submit" class="btn btn-outline-light">📥 Export Profit & Loss to Excel</button>
</form>
</div>
</div>

<script>
document.getElementById('filterSelect').addEventListener('change',()=>{
    location.href='?filter='+document.getElementById('filterSelect').value;
});

new Chart(document.getElementById('dayChart'),{
type:'line',
data:{
labels:<?= json_encode(array_column($dayRevenueData,'day')) ?>,
datasets:[{
label:'Revenue Per Day (₹)',
data:<?= json_encode(array_column($dayRevenueData,'total')) ?>,
borderColor:'#3b82f6',
backgroundColor:'rgba(59,130,246,0.2)',
fill:true,
tension:0.3,
borderWidth:3
}]
},
options:{responsive:true,scales:{y:{beginAtZero:true}}}
});

new Chart(document.getElementById('orderChart'),{
type:'line',
data:{
labels:<?= json_encode(array_map(fn($o)=>"Order #".$o['order_id'],$orderRevenueData)) ?>,
datasets:[{
label:'Revenue Per Order (₹)',
data:<?= json_encode(array_column($orderRevenueData,'total')) ?>,
borderColor:'#a238ee',
backgroundColor:'rgba(162,56,238,0.2)',
fill:true,
tension:0.3,
borderWidth:3
}]
},
options:{responsive:true,scales:{y:{beginAtZero:true}}}
});

new Chart(document.getElementById('profitLossChart'),{
type:'bar',
data:{
labels:<?= json_encode(array_column($dayData,'day')) ?>,
datasets:[
{label:'Profit (₹)', data:<?= json_encode(array_column($dayData,'profit')) ?>, backgroundColor:'#10b981'},
{label:'Loss (₹)', data:<?= json_encode(array_map('abs', array_column($dayData,'loss'))) ?>, backgroundColor:'#ef4444'}
]
},
options:{responsive:true,scales:{y:{beginAtZero:true}}}
});

new Chart(document.getElementById('paymentChart'),{
type:'pie',
data:{
labels:<?= json_encode(array_column($paymentData,'payment_method')) ?>,
datasets:[{data:<?= json_encode(array_column($paymentData,'total')) ?>,backgroundColor:['#e6164a','#0d83aa','#fd7e14','#6c757d']}]
},
options:{responsive:true}
});

new Chart(document.getElementById('topItemsChart'),{
type:'bar',
data:{
labels:<?= json_encode(array_column($topItems,'item_name')) ?>,
datasets:[{label:'Quantity Sold', data:<?= json_encode(array_column($topItems,'total_qty')) ?>, backgroundColor:'#d01212'}]
},
options:{responsive:true,scales:{y:{beginAtZero:true}}}
});
</script>
</body>
</html>