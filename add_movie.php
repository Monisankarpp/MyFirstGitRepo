<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("Error: You must be logged in to add a movie.");
}

$desktopPath = "users.json";

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
  $posters = [];

  // Validate input
  if (empty($movieName) || empty($rating)) {
    $error = "All fields are required!";
  } else {
    // Handle multiple file uploads
    if (!empty($_FILES['poster']['name'][0])) {
      $target_dir = "uploads/";
      $posters = [];

      foreach ($_FILES['poster']['name'] as $key => $name) {
        $tempName = $_FILES['poster']['tmp_name'][$key];
        $newImageName = uniqid('poster_', true) . "." . pathinfo($name, PATHINFO_EXTENSION);

        if (move_uploaded_file($tempName, $target_dir . $newImageName)) {
          $posters[] = [
            'id' => uniqid(),  // Generate a unique ID for each image
            'image' => $newImageName
          ];
        }
      }
    }


    if (!isset($error)) {
      $newMovie = [
        'id' => uniqid(),
        'name' => $movieName,
        'rating' => $rating,
        'posters' => $posters
      ];

      // Add the new movie to the user's movie list
      $users[$userIndex]['movies'][] = $newMovie;

      // Save updated user data to the JSON file
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

    <label for="poster">Movie Posters:</label>
    <input type="file" name="poster[]" accept=".jpg, .jpeg, .png" multiple><br>

    <button type="submit">Add Movie</button>
  </form>

  <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>