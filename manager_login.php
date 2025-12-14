<?php
session_start();
include('config.php');

$message = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM managers WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['manager_id'] = $row['id'];
            $_SESSION['manager_username'] = $row['username'];
            header("Location: manager_dashboard.php");
            exit();
        } else {
            $message = "Incorrect password!";
        }
    } else {
        $message = "Manager not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manager Login</title>
<link rel="icon" type="image/jpg" href="img/icon.jpg"/>
<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                url('../img/resaturanthome.jpg') no-repeat center center fixed;
    background-size: cover; 
}

body::before {
    content: "";
    position: absolute;
    top:0; left:0;
    width:100%; height:100%;
    backdrop-filter: blur(6px);
    z-index: 0;
}

.login-box {
    position: relative;
    z-index: 1;
    width: 400px;
    max-width: 90%;
    padding: 40px 25px;
    background: rgba(0,0,0,0.85);
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.6);
    text-align: center;
    color: #fff;
    transition: transform 0.3s;
}

.login-box:hover {
    transform: translateY(-5px);
}

.login-box img {
    width: 100px;
    margin-bottom: 20px;
}

.login-box h2 {
    font-family: "Dancing Script", cursive;
    color: #9f7911ff;
    margin-bottom: 25px;
}

input {
    width: 90%; 
    padding: 12px; 
    margin: 10px 0;
    border: 1px solid #555;
    border-radius: 5px;
    background: #222;
    color: #ff4d4d;
    font-size: 16px;
    transition: border 0.3s;
}

input:focus {
    outline: none;
    border: 1px solid #28a745;
}

button { 
    width: 50%;
    background: #28a745; 
    color: white; 
    border: none; 
    cursor: pointer; 
    display: block; 
    margin: 15px auto;
    border-radius: 5px;
    padding: 10px;
    font-size: 16px;
    transition: 0.3s;
}

button:hover { 
    background: #218838; 
}

.forgot-password {
    display: block;
    margin-top: 10px;
    color: #ff4d4d;
    font-size: 14px;
    text-decoration: none;
    transition: color 0.3s;
}

.forgot-password:hover {
    color: #ff1a1a;
}

.error { 
    color: #ff4d4d; 
    font-size: 14px; 
    margin-top: 10px;
}

@media (max-width: 480px) {
    .login-box {
        width: 90%;
        padding: 25px 15px;
    }
    button { width: 70%; }
}
</style>
</head>
<body>
    <div class="login-box">
        <h2>Manager Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <a href="#" class="forgot-password">Forgot Password?</a>
        <?php if($message) echo "<p class='error'>$message</p>"; ?>
    </div>
</body>
</html>