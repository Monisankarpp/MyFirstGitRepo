<?php
session_start();

$error = ""; // Store error messages
$logFile = __DIR__ . DIRECTORY_SEPARATOR . "error.log"; // Log file
$jsonFile = __DIR__ . DIRECTORY_SEPARATOR . "user.json"; // Users file
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR; // Uploads directory

// Function to log errors
function logError($message)
{
  global $logFile;
  $timestamp = date("Y-m-d H:i:s");
  error_log("[$timestamp] ERROR: $message" . PHP_EOL, 3, $logFile);
}

try {
  // Ensure user is logged in
  if (!isset($_SESSION['user_id'])) {
    throw new Exception("Error: You must be logged in to add a movie.");
  }

  // Create users.json if not exists
  if (!file_exists($jsonFile)) {
    if (file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT)) === false) {
      throw new Exception("Error: Unable to create users.json.");
    }
  }

  // Load users data
  $users = json_decode(file_get_contents($jsonFile), true);
  if ($users === null) {
    throw new Exception("Error: Failed to read or decode users.json.");
  }

  // Find the current user
  $userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));
  if ($userIndex === false) {
    throw new Exception("Error: User not found.");
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieName = trim($_POST['movie_name']);
    $rating = trim($_POST['rating']);
    $posters = [];

    // Validate inputs
    if (empty($movieName)) {
      throw new Exception("Error: Movie name cannot be empty.");
    }
    if (empty($rating) || !is_numeric($rating) || $rating < 0 || $rating > 10) {
      throw new Exception("Error: Rating must be a number between 0 and 10.");
    }

    // Handle file uploads
    if (!empty($_FILES['poster']['name'][0])) {
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 777, true);
      }

      $maxFileSize = 2 * 1024 * 1024; // 2MB limit
      $allowedExtensions = ['jpg', 'jpeg', 'png'];

      foreach ($_FILES['poster']['name'] as $key => $name) {
        $tempName = $_FILES['poster']['tmp_name'][$key];
        $fileSize = $_FILES['poster']['size'][$key];
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        // Validate file type and size
        if (!in_array($extension, $allowedExtensions)) {
          throw new Exception("Error: Only JPG, JPEG, and PNG files are allowed.");
        }
        if ($fileSize > $maxFileSize) {
          throw new Exception("Error: File '$name' exceeds the 2MB size limit.");
        }

        // Generate unique name and move file
        $newImageName = uniqid('poster_', true) . ".$extension";
        if (!move_uploaded_file($tempName, $uploadDir . $newImageName)) {
          throw new Exception("Error: Failed to upload one or more images.");
        }

        $posters[] = [
          'id' => uniqid(),
          'image' => $newImageName
        ];
      }
    }

    // Add new movie
    $newMovie = [
      'id' => uniqid(),
      'name' => $movieName,
      'rating' => $rating,
      'posters' => $posters
    ];
    $users[$userIndex]['movies'][] = $newMovie;

    // Save updated data
    if (!file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT))) {
      throw new Exception("Error: Failed to save movie data.");
    }

    header("Location: dashboard.php");
    exit();
  }
} catch (Exception $e) {
  $error = $e->getMessage();
  logError($error);
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

    <!-- Show error message if any -->
    <p class="error" id="errorMessage" <?php if (!empty($error))
      echo 'style="display:block;"'; ?>>
      <?php echo $error; ?>
    </p>

    <form action="" method="POST" enctype="multipart/form-data">
      <label for="movie_name">Movie Name:</label>
      <input type="text" name="movie_name" required>

      <label for="rating">Rating (0-10):</label>
      <input type="number" name="rating" min="0" max="10" step="0.1" required>

      <label for="poster">Movie Posters (Max 2MB per file):</label>
      <input type="file" name="poster[]" accept=".jpg, .jpeg, .png" multiple>

      <button type="submit">Add Movie</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
  </div>

</body>

</html>