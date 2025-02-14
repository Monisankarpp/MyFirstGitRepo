<?php
$errorMessages = []; // Store all error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $password = $_POST['password'];

  // Validate Username
  if (empty($username) || strlen($username) < 6) {
    $errorMessages['username'] = "Username must be at least 6 characters.";
  }

  // Validate Email
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMessages['email'] = "Enter a valid email address.";
  }

  // Validate Phone Number
  if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
    $errorMessages['phone'] = "Phone number must be exactly 10 digits.";
  }

  // Validate Password
  if (empty($password) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/', $password)) {
    $errorMessages['password'] = "Password must be at least 6 characters, include 1 uppercase, 1 lowercase, 1 number & 1 special character.";
  }

  // If all validations pass, than storing user
  if (empty($errorMessages)) {
    $users = json_decode(file_get_contents('users.json'), true) ?: [];

    // Check if email is already registered
    foreach ($users as $user) {
      if ($user['email'] == $email) {
        $errorMessages['email'] = "Email already registered!";
        break;
      }
    }

    // If no email conflict, save user
    if (empty($errorMessages)) {
      $newUser = [
        'id' => uniqid(),
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'password' => password_hash($password, PASSWORD_DEFAULT),
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
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="CSS/signup.css">
</head>

<body>
  <div class="container">
    <h2>Create Your Account</h2>

    <form method="POST" onsubmit="return validateForm()">
      <input type="text" name="username" id="username" placeholder="Username"
        value="<?php echo $_POST['username'] ?? ''; ?>" oninput="validateForm()">
      <p class="error" id="usernameError"><?php echo $errorMessages['username'] ?? ''; ?></p>

      <input type="email" name="email" id="email" placeholder="Email" value="<?php echo $_POST['email'] ?? ''; ?>"
        oninput="validateForm()">
      <p class="error" id="emailError"><?php echo $errorMessages['email'] ?? ''; ?></p>

      <input type="tel" name="phone" id="phone" placeholder="Phone Number" value="<?php echo $_POST['phone'] ?? ''; ?>"
        maxlength="10" oninput="validateForm()">
      <p class="error" id="phoneError"><?php echo $errorMessages['phone'] ?? ''; ?></p>

      <input type="password" name="password" id="password" placeholder="Password" oninput="validateForm()">
      <p class="error" id="passwordError"><?php echo $errorMessages['password'] ?? ''; ?></p>

      <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="index.php">Login here</a></p>
  </div>
  <script src="JS/signup.js"></script>
</body>

</html>