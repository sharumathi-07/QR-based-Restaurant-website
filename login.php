<?php
session_start();
$conn = new mysqli("127.0.0.1", "root", "", "restaurant_db"); // ✅ default port

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // encrypt password

    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "❌ Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Login</title>
    <link rel="icon" type="image/jpg" href="img/icon.jpg"/>

    <style>
        
        body { 
            font-family: Arial, sans-serif; 
            background: url('') no-repeat center center fixed;
            
            background-size: cover; 
            
        }
        .login-box {
            width: 400px; height:400px; margin: 100px auto; padding: 20px;
            background-color:black; border-radius: 8px;
        }
        .login-box h2{
            color:white;
            text-align:center;
        }
        input, button { width: 90%; padding: 10px; margin: 8px 0; }
        button { width:50%;background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .error { color: red; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Kitchen Admin Login</h2>
    </br></br></br>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</body>
</html>
