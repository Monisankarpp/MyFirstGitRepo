<?php
session_start();

try {
  $jsonFile = __DIR__ . DIRECTORY_SEPARATOR . 'user.json';
  $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
  $logFile = __DIR__ . DIRECTORY_SEPARATOR . 'error.log'; // Log file for errors

  // Create users.json if not exists
  if (!file_exists($jsonFile)) {
    if (file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT)) === false) {
      throw new Exception("Error: Unable to create users.json.");
    }
  }

  // Load users data
  $users = json_decode(file_get_contents($jsonFile), true);
  if ($users === null) {
    throw new Exception("Error: Failed to load user data.");
  }

  // Ensure user is logged in
  if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
  }

  // Find current user
  $currentUser = null;
  foreach ($users as $index => $user) {
    if ($user['id'] == $_SESSION['user_id']) {
      $currentUser = &$users[$index];
      break;
    }
  }

  if (!$currentUser) {
    throw new Exception("Error: User not found.");
  }

  $errorMessage = "";

  // Handle form submission
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);

    // Validate input
    if (empty($username)) {
      $errorMessage = "Error: Username cannot be empty.";
    } elseif (empty($phone)) {
      $errorMessage = "Error: Phone number cannot be empty.";
    } elseif (!preg_match("/^[a-zA-Z0-9_ ]+$/", $username)) {
      $errorMessage = "Error: Username can only contain letters, numbers, spaces, and underscores.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
      $errorMessage = "Error: Phone number must be exactly 10 digits.";
    } else {
      // Update user data
      $currentUser['username'] = $username;
      $currentUser['phone'] = $phone;

      // Handle profile photo upload
      if (!empty($_FILES['profile_photo']['name'])) {
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }

        $profilePhoto = $_FILES["profile_photo"]["name"];
        $imageExtension = strtolower(pathinfo($profilePhoto, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($imageExtension, $allowedExtensions)) {
          $errorMessage = "Error: Only JPG, JPEG, and PNG files are allowed.";
        } elseif ($_FILES["profile_photo"]["size"] > 2 * 1024 * 1024) { // 2MB limit
          $errorMessage = "Error: File size exceeds 2MB.";
        } else {
          $newImageName = uniqid('profile_', true) . "." . $imageExtension;
          $targetFile = $uploadDir . $newImageName;

          if (!move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
            $errorMessage = "Error: Failed to upload file.";
          } else {
            $currentUser['profile_photo'] = $newImageName;
          }
        }
      }

      // Save changes if no errors
      if (empty($errorMessage)) {
        if (file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT)) === false) {
          throw new Exception("Error: Failed to save data.");
        }
        header("Location: dashboard.php");
        exit();
      }
    }
  }
} catch (Exception $e) {
  $errorMessage = $e->getMessage();

  // Log error to file
  file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $errorMessage . PHP_EOL, FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Edit Profile</title>
  <link rel="stylesheet" href="CSS/add-movie.css">
</head>

<body>
  <h2>Edit Profile</h2>

  <?php if (!empty($errorMessage)): ?>
    <p style="color: red;"><?php echo $errorMessage; ?></p>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Username:</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" required><br>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($currentUser['phone']); ?>" required><br>

    <label>Profile Photo (Max 2MB, JPG/JPEG/PNG):</label>
    <input type="file" name="profile_photo" accept=".png, .jpeg, .jpg"><br><br>

    <button type="submit">Save Changes</button>
  </form>

  <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>