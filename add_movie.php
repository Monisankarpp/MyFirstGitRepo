<?php
session_start();

$error = ""; // Store error messages

try {
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
    throw new Exception("You must be logged in to add a movie.");
  }

  $desktopPath = "users.json";

  // Check if users.json exists
  if (!file_exists($desktopPath)) {
    throw new Exception("Error: users.json file not found.");
  }

  // Load user data
  $usersData = file_get_contents($desktopPath);
  $users = json_decode($usersData, true);

  if ($users === null) {
    throw new Exception("Error: Failed to read or decode users.json.");
  }

  // Find the current user in the users array
  $userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));
  if ($userIndex === false) {
    throw new Exception("Error: User not found.");
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieName = trim($_POST['movie_name']);
    $rating = trim($_POST['rating']);
    $posters = [];

    // Validate input
    if (empty($movieName) || empty($rating)) {
      throw new Exception("All fields are required!");
    }

    // Handle multiple file uploads with size constraints
    if (!empty($_FILES['poster']['name'][0])) {
      $target_dir = "uploads/";
      $posters = [];
      $maxFileSize = 2 * 1024 * 1024; // 2MB limit

      foreach ($_FILES['poster']['name'] as $key => $name) {
        $tempName = $_FILES['poster']['tmp_name'][$key];
        $fileSize = $_FILES['poster']['size'][$key];

        // Check file size
        if ($fileSize > $maxFileSize) {
          throw new Exception("Error: File '$name' exceeds the 2MB size limit.");
        }

        $newImageName = uniqid('poster_', true) . "." . pathinfo($name, PATHINFO_EXTENSION);

        if (!move_uploaded_file($tempName, $target_dir . $newImageName)) {
          throw new Exception("Error: Failed to upload one or more images.");
        }

        $posters[] = [
          'id' => uniqid(),  // Generate a unique ID for each image
          'image' => $newImageName
        ];
      }
    }

    $newMovie = [
      'id' => uniqid(),
      'name' => $movieName,
      'rating' => $rating,
      'posters' => $posters
    ];

    // Add the new movie to the user's movie list
    $users[$userIndex]['movies'][] = $newMovie;

    // Save updated user data to the JSON file
    if (!file_put_contents($desktopPath, json_encode($users, JSON_PRETTY_PRINT))) {
      throw new Exception("Error: Failed to save movie data.");
    }

    header("Location: dashboard.php");
    exit();
  }
} catch (Exception $e) {
  $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Favorite Movie</title>
  <link rel="stylesheet" href="CSS/add-movie.css">
</head>

<body>

  <div class="container">
    <h2>Add Favorite Movie</h2>

    <!-- Show error message only if an error exists -->
    <p class="error" id="errorMessage" <?php if (!empty($error))
      echo 'style="display:block;"'; ?>>
      <?php echo $error; ?>
    </p>

    <form action="" method="POST" enctype="multipart/form-data">
      <label for="movie_name">Movie Name:</label>
      <input type="text" name="movie_name" required>

      <label for="rating">Rating:</label>
      <input type="text" name="rating" required>

      <label for="poster">Movie Posters (Max 2MB per file):</label>
      <input type="file" name="poster[]" accept=".jpg, .jpeg, .png" multiple>

      <button type="submit">Add Movie</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
  </div>

</body>

</html>