<?php
session_start();


$valid_username = "waiter";
$valid_password = "waiter_123";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['waiter'] = $username;
        header("Location: waiter.php");
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Waiter Login</title>
    <link rel="icon" type="image/jpg" href="img/icon.jpg"/>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('img/waiter-bg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            text-align: center;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover { background: #218838; }
        .error { color: red; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Waiter Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
