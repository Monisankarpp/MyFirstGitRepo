<?php
session_start();

// Define log file path in a platform-independent way
$logFile = __DIR__ . DIRECTORY_SEPARATOR . 'error.log';

try {
  // Check if user is logged in
  if (!isset($_SESSION['user_id'])) {
    throw new Exception("Error: You must be logged in.");
  }

  $movieId = $_GET['movie_id'] ?? null;
  if (!$movieId) {
    throw new Exception("Error: Invalid movie ID.");
  }

  // Check if users.json exists
  $userFile = __DIR__ . DIRECTORY_SEPARATOR . 'user.json';
  if (!file_exists($userFile)) {
    throw new Exception("Error: Data file not found.");
  }

  $users = json_decode(file_get_contents($userFile), true);
  if ($users === null) {
    throw new Exception("Error: Failed to load user data.");
  }

  // Find user index
  $userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));
  if ($userIndex === false) {
    throw new Exception("Error: User not found.");
  }

  // Find movie index
  $movies = &$users[$userIndex]['movies'];
  $movieIndex = array_search($movieId, array_column($movies, 'id'));

  if ($movieIndex === false) {
    throw new Exception("Error: Movie not found.");
  }

  $movie = &$movies[$movieIndex];

  // Handle form submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieName = trim($_POST['movie_name']);
    $rating = trim($_POST['rating']);

    // Validate movie name
    if (empty($movieName)) {
      throw new Exception("Error: Movie name cannot be blank.");
    }

    // Validate rating
    if (!is_numeric($rating) || $rating < 0 || $rating > 10) {
      throw new Exception("Error: Rating must be between 0 and 10.");
    }

    // Handle new poster uploads
    if (!empty($_FILES['posters']['name'][0])) {
      $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
      }

      foreach ($_FILES['posters']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['posters']['size'][$key] > 2 * 1024 * 1024) { // 2MB limit
          throw new Exception("Error: File size exceeds 2MB.");
        }

        $posterName = $_FILES['posters']['name'][$key];
        $imageExtension = pathinfo($posterName, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array(strtolower($imageExtension), $allowedExtensions)) {
          throw new Exception("Error: Only JPG, JPEG, and PNG files are allowed.");
        }

        $newImageName = uniqid('poster_', true) . "." . strtolower($imageExtension);
        $posterPath = $uploadDir . $newImageName;

        if (!move_uploaded_file($tmpName, $posterPath)) {
          throw new Exception("Error: Failed to upload file.");
        }

        $movie['posters'][] = [
          'id' => uniqid(),
          'image' => $newImageName
        ];
      }
    }

    // Update movie details
    $movie['name'] = $movieName;
    $movie['rating'] = $rating;

    // Save updated data to JSON
    if (file_put_contents($userFile, json_encode($users, JSON_PRETTY_PRINT)) === false) {
      throw new Exception("Error: Failed to save data.");
    }

    // Redirect back to dashboard
    header("Location: dashboard.php");
    exit();
  }
} catch (Exception $e) {
  // Log error details
  $errorMessage = "[" . date("Y-m-d H:i:s") . "] " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . PHP_EOL;
  error_log($errorMessage, 3, $logFile);

  // Display user-friendly message
  die("An error occurred. Please check the log file.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Edit Movie</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="CSS/edit-movie.css">
</head>

<body>
  <div class="container mt-5">
    <h2>Edit Movie</h2>

    <form action="" method="POST" enctype="multipart/form-data">
      <label>Movie Name:</label>
      <input type="text" name="movie_name" value="<?php echo htmlspecialchars($movie['name']); ?>" required
        class="form-control mb-2">

      <label>Rating (0-10):</label>
      <input type="number" name="rating" min="0" max="10" step="0.1"
        value="<?php echo htmlspecialchars($movie['rating']); ?>" required class="form-control mb-2">

      <h3>Current Posters:</h3>
      <div id="posterContainer">
        <?php if (!empty($movie['posters'])): ?>
          <?php foreach ($movie['posters'] as $poster): ?>
            <div class="poster-box d-inline-block me-2">
              <img src="uploads/<?php echo htmlspecialchars($poster['image']); ?>" width="100" height="150">
              <button type="button" class="btn btn-danger btn-sm mt-1 delete-btn"
                data-poster-id="<?php echo $poster['id']; ?>" data-bs-toggle="modal"
                data-bs-target="#deleteModal">❌</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No posters available.</p>
        <?php endif; ?>
      </div>

      <label>Add New Posters (Max 2MB per file):</label>
      <input type="file" name="posters[]" multiple class="form-control mb-2" accept=".png,.jpeg,.jpg">

      <button type="submit" class="btn btn-primary">Update Movie</button>
    </form>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
  </div>

  <!-- Bootstrap Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-footer">
          <p>Are you sure you want to delete this poster?</p>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    let posterIdToDelete = null;

    $('.delete-btn').on('click', function () {
      posterIdToDelete = $(this).data('poster-id');
    });

    $('#confirmDeleteBtn').on('click', function () {
      if (posterIdToDelete) {
        $.post('delete_poster.php', { poster_id: posterIdToDelete, movie_id: "<?php echo $movieId; ?>" }, function (response) {
          if (response === "success") {
            $('button[data-poster-id="' + posterIdToDelete + '"]').closest('.poster-box').remove();
            $('#deleteModal').modal('hide');
          } else {
            alert("Error deleting poster.");
          }
        });
      }
    });
  </script>
</body>

</html>