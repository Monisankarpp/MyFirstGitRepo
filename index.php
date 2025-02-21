<?php
session_start();

include_once "db_connection.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $passwordHash = $password;

  $stmt = $conn->prepare("SELECT id, passwords FROM User WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($user_id, $hashedPassword);
  $stmt->fetch();

  if ($stmt->num_rows > 0 && $passwordHash == $hashedPassword) {
    $_SESSION['user_id'] = $user_id;
    header("Location: dashboard.php");
    exit();
    // echo "User found! Stored password hash " . $hashedPassword . "<br>";
    // echo "Enter password " . $passwordHash . "<br>";
    // echo (password_verify($passwordHash, $hashedPassword) ? "success" : "failed");
  } else {
    $error = "Invalid email or password!";
  }
  $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
  <title>Login</title>
  <link rel="stylesheet" href="CSS/login.css">
</head>

<body>
  <div class="container">
    <h2>Login to Your Account</h2>
    <?php if (isset($error))
      echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Email" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
  </div>
  <script src="JS/login.js"></script>
</body>

</html>