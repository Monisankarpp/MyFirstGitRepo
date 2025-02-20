<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $users = json_decode(file_get_contents('user.json'), true) ?: [];

  foreach ($users as $user) {
    if ($user['email'] == $email && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      header("Location: dashboard.php");
      exit();
    }
  }
  $error = "Invalid email or password!";
}
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