<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("Error: You must be logged in to add a movie.");
}

$desktopPath = "/var/www/html/project2/users.json";

// Check if users.json exists
if (!file_exists($desktopPath)) {
  die("Error: users.json file not found at $desktopPath");
}

// Load user data
$usersData = file_get_contents($desktopPath);
$users = json_decode($usersData, true);

if ($users === null) {
  die("Error: Failed to read or decode users.json");
}

// Find the current user in the users array
$userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));
if ($userIndex === false) {
  die("Error: User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $movieName = trim($_POST['movie_name']);
  $rating = trim($_POST['rating']);
  $poster = '';

  // Validate input
  if (empty($movieName) || empty($rating)) {
    $error = "All fields are required!";
  } else {
    // Handle single file upload
    if (!empty($_FILES['poster']['name'])) {
      $target_dir = "uploads/";
      $originalName = $_FILES['poster']['name'];
      $tempName = $_FILES['poster']['tmp_name'];
      $imageExtension = pathinfo($originalName, PATHINFO_EXTENSION);

      // Generate a unique name for the image
      $newImageName = uniqid('poster_', true) . "." . strtolower($imageExtension);

      // Move the file to the uploads directory
      if (move_uploaded_file($tempName, $target_dir . $newImageName)) {
        $poster = $newImageName;  // Store the uploaded file name
      } else {
        $error = "Error uploading file: " . htmlspecialchars($originalName);
      }
    }

    if (!isset($error)) {
      $newMovie = [
        'id' => uniqid(),
        'name' => $movieName,
        'rating' => $rating,
        'poster' => $poster
      ];

      $users[$userIndex]['movies'][] = $newMovie;

      if (file_put_contents($desktopPath, json_encode($users, JSON_PRETTY_PRINT))) {
        header("Location: dashboard.php");
        exit();
      } else {
        $error = "Error: Failed to save movie data.";
      }
    }
  }
}
?>

<!-- HTML Start -->
<!DOCTYPE html>
<html>

<head>
  <title>Add Favorite Movie</title>
  <link rel="stylesheet" href="CSS/add-movie.css">
</head>

<body>
  <h2>Add Favorite Movie</h2>

  <?php if (isset($error)): ?>
    <p style="color:red;"><?php echo $error; ?></p>
  <?php endif; ?>

  <form action="" method="POST" enctype="multipart/form-data">
    <label for="movie_name">Movie Name:</label>
    <input type="text" name="movie_name" required><br>

    <label for="rating">Rating:</label>
    <input type="text" name="rating" required><br>

    <label for="poster">Movie Poster:</label>
    <!-- Changed to single file upload, removed 'multiple' -->
    <input type="file" name="poster" accept=".jpg, .jpeg, .png" required><br>

    <button type="submit">Add Movie</button>
  </form>

  <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>