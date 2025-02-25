<?php
session_start();

$logFile = __DIR__ . DIRECTORY_SEPARATOR . "error.log";
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

include_once "db_connection.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$errorMessage = "";

// Fetch current user data
$sql = "SELECT username, phone, profile_photo FROM User WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $phone = trim($_POST['phone']);

  // Validation
  if (empty($username)) {
    $errorMessage = "Error: Username cannot be empty.";
  } elseif (empty($phone)) {
    $errorMessage = "Error: Phone number cannot be empty.";
  } elseif (!preg_match("/^[a-zA-Z0-9_ ]+$/", $username)) {
    $errorMessage = "Error: Username can only contain letters, numbers, spaces, and underscores.";
  } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errorMessage = "Error: Phone number must be exactly 10 digits.";
  } else {
    // Handle profile photo upload
    if (!empty($_FILES['profile_photo']['name'])) {
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 777, true);
      }

      $profilePhoto = $_FILES["profile_photo"]["name"];
      $imageExtension = strtolower(pathinfo($profilePhoto, PATHINFO_EXTENSION));
      $allowedExtensions = ['jpg', 'jpeg', 'png'];

      if (!in_array($imageExtension, $allowedExtensions)) {
        $errorMessage = "Error: Only JPG, JPEG, and PNG files are allowed.";
      } elseif ($_FILES["profile_photo"]["size"] > 2 * 1024 * 1024) {
        $errorMessage = "Error: File size exceeds 2MB.";
      } else {
        $newImageName = uniqid('profile_', true) . "." . $imageExtension;
        $targetFile = $uploadDir . $newImageName;

        if (!move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
          $errorMessage = "Error: Failed to upload file.";
        } else {
          $sql = "UPDATE User SET username = ?, phone = ?, profile_photo = ?, profile_update_date = NOW() WHERE id = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("sssi", $username, $phone, $newImageName, $user_id);
        }
      }
    } else {
      $sql = "UPDATE User SET username = ?, phone = ?, profile_update_date = NOW() WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssi", $username, $phone, $user_id);
    }

    if (empty($errorMessage)) {
      if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
      } else {
        $errorMessage = "Error: Failed to update profile.";
      }
      $stmt->close();
    }
  }
}
$conn->close();
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
    <p style="color: red;"> <?php echo $errorMessage; ?> </p>
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