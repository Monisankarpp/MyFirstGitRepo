<?php
session_start();

$logFile = __DIR__ . DIRECTORY_SEPARATOR . "error.log";
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR;

// Database connection
include_once "db_connection.php";

// Function to log errors
function logError($message)
{
  global $logFile;
  $timestamp = date("Y-m-d H:i:s");
  error_log("[$timestamp] ERROR: $message" . PHP_EOL, 3, $logFile);
}

$error = "";

try {
  if (!isset($_SESSION['user_id'])) {
    throw new Exception("Error: You must be logged in to add a movie.");
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieName = trim($_POST['movie_name']);
    $rating = trim($_POST['rating']);
    $userId = $_SESSION['user_id'];

    if (empty($movieName)) {
      throw new Exception("Error: Movie name cannot be empty.");
    }
    if (empty($rating) || !is_numeric($rating) || $rating < 0 || $rating > 10) {
      throw new Exception("Error: Rating must be a number between 0 and 10.");
    }

    // Insert movie details into database
    $stmt = $conn->prepare("INSERT INTO movies (user_id, movie_name, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $userId, $movieName, $rating);
    if (!$stmt->execute()) {
      throw new Exception("Error: Could not add movie.");
    }
    $movieId = $stmt->insert_id;
    $stmt->close();

    // Handle file uploads
    if (!empty($_FILES['poster']['name'][0])) {
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 777, true);
      }

      $maxFileSize = 2 * 1024 * 1024;
      $allowedExtensions = ['jpg', 'jpeg', 'png'];

      foreach ($_FILES['poster']['name'] as $key => $name) {
        $tempName = $_FILES['poster']['tmp_name'][$key];
        $fileSize = $_FILES['poster']['size'][$key];
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
          throw new Exception("Error: Only JPG, JPEG, and PNG files are allowed.");
        }
        if ($fileSize > $maxFileSize) {
          throw new Exception("Error: File '$name' exceeds the 2MB size limit.");
        }

        $newImageName = uniqid('poster_', true) . ".$extension";
        if (!move_uploaded_file($tempName, $uploadDir . $newImageName)) {
          throw new Exception("Error: Failed to upload one or more images.");
        }

        // Insert poster path into database
        $stmt = $conn->prepare("INSERT INTO movie_posters (movie_id, image) VALUES (?, ?)");
        $stmt->bind_param("is", $movieId, $newImageName);
        if (!$stmt->execute()) {
          throw new Exception("Error: Could not save poster information.");
        }
        $stmt->close();
      }
    }

    header("Location: dashboard.php");
    exit();
  }
} catch (Exception $e) {
  $error = $e->getMessage() . " at line " . $e->getLine();
  logError($error);
}
$conn->close();
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