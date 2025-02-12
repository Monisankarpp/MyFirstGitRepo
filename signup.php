<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  if (empty($username) || empty($email) || empty($phone) || empty($_POST['password'])) {
    $error = "All fields are required!";
  } else {
    $users = json_decode(file_get_contents('users.json'), true) ?: [];

    foreach ($users as $user) {
      if ($user['email'] == $email) {
        $error = "Email already registered!";
        break;
      }
    }

    if (!isset($error)) {
      $newUser = [
        'id' => uniqid(),
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'password' => $password,
        'profile_photo' => 'default.png',
        'movies' => []
      ];
      $users[] = $newUser;
      file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
      header("Location: index.php");
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Sign Up</title>
  <link rel="stylesheet" href="CSS/signup.css">
</head>

<body>
  <div class="container">
    <h2>Create Your Account</h2>
    <?php if (isset($error))
      echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required><br>
      <input type="email" name="email" placeholder="Email" required><br>
      <input type="tel" name="phone" placeholder="Phone Number" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="index.php">Login here</a></p>
  </div>
</body>

</html>