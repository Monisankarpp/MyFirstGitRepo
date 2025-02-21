<?php
session_start();
require 'db_connection.php'; // Include database connection

$logFile = __DIR__ . DIRECTORY_SEPARATOR . 'error.log';

try {
  if (!isset($_SESSION['user_id'])) {
    throw new Exception("Error: You must be logged in.");
  }

  $movieId = $_GET['movie_id'] ?? null;
  if (!$movieId) {
    throw new Exception("Error: Invalid movie ID.");
  }

  // Fetch movie details from the database
  $query = mysqli_prepare($conn, "SELECT * FROM movies WHERE id = ? AND user_id = ?");
  mysqli_stmt_bind_param($query, "ii", $movieId, $_SESSION['user_id']);
  mysqli_stmt_execute($query);
  $result = mysqli_stmt_get_result($query);
  $movie = mysqli_fetch_assoc($result);

  if (!$movie) {
    throw new Exception("Error: Movie not found.");
  }

  // Fetch movie posters
  $query = mysqli_prepare($conn, "SELECT * FROM movie_posters WHERE movie_id = ?");
  mysqli_stmt_bind_param($query, "i", $movieId);
  mysqli_stmt_execute($query);
  $result = mysqli_stmt_get_result($query);
  $posters = mysqli_fetch_all($result, MYSQLI_ASSOC);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieName = trim($_POST['movie_name']);
    $rating = trim($_POST['rating']);

    if (empty($movieName) || !is_numeric($rating) || $rating < 0 || $rating > 10) {
      throw new Exception("Error: Invalid input.");
    }

    // Update movie details
    $query = mysqli_prepare($conn, "UPDATE movies SET movie_name = ?, rating = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($query, "sdii", $movieName, $rating, $movieId, $_SESSION['user_id']);
    mysqli_stmt_execute($query);

    // Handle poster uploads
    if (!empty($_FILES['posters']['name'][0])) {
      $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 777, true);
      }

      foreach ($_FILES['posters']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['posters']['size'][$key] > 2 * 1024 * 1024) {
          throw new Exception("Error: File size exceeds 2MB.");
        }

        $imageExtension = pathinfo($_FILES['posters']['name'][$key], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array(strtolower($imageExtension), $allowedExtensions)) {
          throw new Exception("Error: Invalid file type.");
        }

        $newImageName = uniqid('poster_', true) . '.' . strtolower($imageExtension);
        $posterPath = $uploadDir . $newImageName;

        if (!move_uploaded_file($tmpName, $posterPath)) {
          throw new Exception("Error: Failed to upload file.");
        }

        // Insert into database
        $query = mysqli_prepare($conn, "INSERT INTO movie_posters (movie_id, image) VALUES (?, ?)");
        mysqli_stmt_bind_param($query, "is", $movieId, $newImageName);
        mysqli_stmt_execute($query);
      }
    }

    header("Location: dashboard.php");
    exit();
  }
} catch (Exception $e) {
  error_log("[" . date("Y-m-d H:i:s") . "] " . $e->getMessage() . $e->getLine() . PHP_EOL, 3, $logFile);
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
      <input type="text" name="movie_name" value="<?php echo htmlspecialchars($movie['movie_name']); ?>" required
        class="form-control mb-2">
      <label>Rating (0-10):</label>
      <input type="number" name="rating" min="0" max="10" step="0.1"
        value="<?php echo htmlspecialchars($movie['rating']); ?>" required class="form-control mb-2">
      <h3>Current Posters:</h3>
      <div id="posterContainer">
        <?php foreach ($posters as $poster): ?>
          <div class="poster-box d-inline-block me-2">
            <img src="uploads/<?php echo htmlspecialchars($poster['image']); ?>" width="100" height="150">
            <button type="button" class="btn btn-danger btn-sm mt-1 delete-btn"
              data-poster-id="<?php echo $poster['id']; ?>" data-bs-toggle="modal"
              data-bs-target="#deleteModal">❌</button>
          </div>
        <?php endforeach; ?>
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