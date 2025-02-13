<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  die("Error: You must be logged in.");
}

$movieId = $_GET['movie_id'];
$users = json_decode(file_get_contents('users.json'), true);

// Find user index
$userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));
if ($userIndex === false) {
  die("Error: User not found.");
}

// Find movie index
$movies = &$users[$userIndex]['movies'];
$movieIndex = array_search($movieId, array_column($movies, 'id'));

if ($movieIndex === false) {
  die("Error: Movie not found.");
}

$movie = &$movies[$movieIndex];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $movieName = trim($_POST['movie_name']);
  $rating = trim($_POST['rating']);

  // Handle new poster uploads
  if (!empty($_FILES['posters']['name'][0])) {
    $uploadDir = 'uploads/';
    foreach ($_FILES['posters']['tmp_name'] as $key => $tmpName) {
      $posterName = $_FILES['posters']['name'][$key];
      $imageExtension = pathinfo($posterName, PATHINFO_EXTENSION);
      $newImageName = uniqid('poster_', true) . "." . strtolower($imageExtension);
      $posterPath = $uploadDir . $newImageName;

      if (move_uploaded_file($tmpName, $posterPath)) {
        $movie['posters'][] = [
          'id' => uniqid(),
          'image' => $newImageName
        ];
      }
    }
  }

  // Update movie details
  $movie['name'] = $movieName;
  $movie['rating'] = $rating;

  // Save updated data to JSON
  file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));

  // Redirect back to dashboard
  header("Location: dashboard.php");
  exit();
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

      <label>Rating:</label>
      <input type="text" name="rating" value="<?php echo htmlspecialchars($movie['rating']); ?>" required
        class="form-control mb-2">

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

      <label>Add New Posters:</label>
      <input type="file" name="posters[]" multiple class="form-control mb-2">

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

    // Capture the poster ID when delete button is clicked
    $('.delete-btn').on('click', function () {
      posterIdToDelete = $(this).data('poster-id');
    });

    // Handle delete confirmation
    $('#confirmDeleteBtn').on('click', function () {
      if (posterIdToDelete) {
        $.ajax({
          url: 'delete_poster.php',
          type: 'POST',
          data: { poster_id: posterIdToDelete, movie_id: "<?php echo $movieId; ?>" },
          success: function (response) {
            if (response === "success") {
              $('button[data-poster-id="' + posterIdToDelete + '"]').closest('.poster-box').remove();
              $('#deleteModal').modal('hide');
            } else {
              alert("Error deleting poster.");
            }
          }
        });
      }
    });
  </script>
</body>

</html>