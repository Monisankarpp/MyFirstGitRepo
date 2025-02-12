<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$users = json_decode(file_get_contents('users.json'), true);

foreach ($users as &$user) {
  if ($user['id'] == $_SESSION['user_id']) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $user['username'] = trim($_POST['username']);
      $user['phone'] = trim($_POST['phone']);

      if ($_FILES['profile_photo']['name']) {
        $target_dir = "uploads/";
        $profile_photo = $_FILES["profile_photo"]["name"];
        $target_file = $target_dir . $profile_photo;
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file);
        $user['profile_photo'] = $profile_photo;
      }

      file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
      header("Location: dashboard.php");
      exit();
    }
    $currentUser = $user;
    break;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Edit Profile</title>
  <link rel="stylesheet" href="CSS/add-movie.css">
</head>

<body>
  <h2>Edit Profile</h2>
  <form method="POST" enctype="multipart/form-data">
    Username: <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>"
      required><br>
    Phone: <input type="text" name="phone" value="<?php echo htmlspecialchars($currentUser['phone']); ?>" required><br>
    Profile Photo: <input type="file" name="profile_photo"><br><br>
    <button type="submit">Save Changes</button>
  </form>
  <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>