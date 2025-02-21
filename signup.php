<?php
session_start();
$errorMessages = []; // Store all error messages

include_once "db_connection.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $uuid = uniqid();

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

  // If all validations pass, proceed
  if (empty($errorMessages)) {
    // Check if email is already registered
    $stmt = $conn->prepare("SELECT id FROM User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $errorMessages['email'] = "Email already registered!";
    } else {
      // Insert new user
      // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $hashedPassword = $password;
      $stmt = $conn->prepare("INSERT INTO User (uuid, username, email, profile_photo, phone, passwords) VALUES (?, ?, ?, ?, ?, ?)");
      $profile_photo = 'default.png';
      $stmt->bind_param("ssssss", $uuid, $username, $email, $profile_photo, $phone, $hashedPassword);

      if ($stmt->execute()) {
        // Redirect to login page
        header("Location: index.php");
        exit();
      } else {
        $errorMessages['general'] = "Error: Unable to register user.";
      }
    }
    $stmt->close();
  }
}
$conn->close();
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
        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" oninput="validateForm()">
      <p class="error" id="usernameError"><?php echo $errorMessages['username'] ?? ''; ?></p>

      <input type="email" name="email" id="email" placeholder="Email"
        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" oninput="validateForm()">
      <p class="error" id="emailError"><?php echo $errorMessages['email'] ?? ''; ?></p>

      <input type="tel" name="phone" id="phone" placeholder="Phone Number"
        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" maxlength="10" oninput="validateForm()">
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