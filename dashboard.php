<?php
session_start();

$logFile = __DIR__ . DIRECTORY_SEPARATOR . "error.log";

function logError($message)
{
  global $logFile;
  $timestamp = date("Y-m-d H:i:s");
  error_log("[$timestamp] ERROR: $message" . PHP_EOL, 3, $logFile);
}

include_once "db_connection.php";

try {
  if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
  }

  $userId = $_SESSION['user_id'];

  // Retrieve user data from database
  $stmt = $conn->prepare("SELECT username, email, phone, profile_photo FROM User WHERE id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($username, $email, $phone, $profilePhoto);
  $stmt->fetch();

  if ($stmt->num_rows === 0) {
    throw new Exception("Error: User not found.");
  }
  $stmt->close();
  // Retrieve movies from database
  $movies = [];
  $stmt = $conn->prepare("SELECT id, movie_name, rating FROM movies WHERE user_id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $movieId = $row['id'];

    // Fetch posters for each movie
    $posterStmt = $conn->prepare("SELECT image FROM movie_posters WHERE movie_id = ?");
    $posterStmt->bind_param("i", $movieId);
    $posterStmt->execute();
    $posterResult = $posterStmt->get_result();

    $posters = [];
    while ($posterRow = $posterResult->fetch_assoc()) {
      $posters[] = $posterRow;
    }
    $posterStmt->close();

    $movies[] = [
      'id' => $row['id'],
      'name' => $row['movie_name'],
      'rating' => $row['rating'],
      'posters' => $posters
    ];
  }
  $stmt->close();


} catch (Exception $e) {
  logError($e->getMessage());
  echo "<p style='color: red; font-weight: bold;'>Something went wrong. Please try again later.</p>";
  exit();
}
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="CSS/dashboard.css">
  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.54);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      max-width: 90%;
      max-height: 90%;
    }

    .close {
      position: absolute;
      top: 10px;
      right: 20px;
      color: white;
      font-size: 30px;
      cursor: pointer;
    }
  </style>
</head>

<body>

  <div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
  </div>

  <div class="profile-info">
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
    <img src="uploads/<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile Photo" width="150">
    <div>
      <a href="edit_profile.php">Edit Profile</a>
      <a href="add_movie.php">Add Favorite Movie</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="movies">
    <h2>Your Favorite Movies</h2>
    <table border="1">
      <tr>
        <th>Posters</th>
        <th>Movie Name</th>
        <th>Rating</th>
        <th>Action</th>
      </tr>

      <?php if (!empty($movies)): ?>
        <?php foreach ($movies as $movie): ?>
          <tr>
            <td>
              <?php if (!empty($movie['posters'])): ?>
                <?php foreach ($movie['posters'] as $poster): ?>
                  <img src="uploads/<?php echo htmlspecialchars($poster['image']); ?>" width="200" height="150"
                    style="margin: 5px; cursor: pointer;"
                    onclick="openModal('uploads/<?php echo htmlspecialchars($poster['image']); ?>')">
                <?php endforeach; ?>
              <?php else: ?>
                <p>No posters uploaded.</p>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($movie['name']); ?></td>
            <td><?php echo htmlspecialchars($movie['rating']); ?></td>
            <td>
              <a class="action-btn" href="edit_movie.php?movie_id=<?php echo $movie['id']; ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4">You haven't added any movies yet!</td>
        </tr>
      <?php endif; ?>
    </table>
  </div>

  <div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
  </div>

  <script>
    function openModal(imageSrc) {
      document.getElementById('modalImage').src = imageSrc;
      document.getElementById('imageModal').style.display = "flex";
    }

    function closeModal() {
      document.getElementById('imageModal').style.display = "none";
    }
  </script>

</body>

</html>