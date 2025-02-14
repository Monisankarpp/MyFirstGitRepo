<?php
session_start();

try {
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
  }

  $jsonFile = 'users.json';

  // Check if users.json exists
  if (!file_exists($jsonFile)) {
    throw new Exception("Error: users.json file not found.");
  }

  // Read and decode JSON data
  $usersData = file_get_contents($jsonFile);
  $users = json_decode($usersData, true);

  if ($users === null) {
    throw new Exception("Error: Failed to read or decode users.json.");
  }

  // Find the current user
  $currentUser = null;
  foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
      $currentUser = $user;
      break;
    }
  }

  if (!$currentUser) {
    throw new Exception("Error: User not found.");
  }

} catch (Exception $e) {
  die("<p style='color: red; font-weight: bold;'>{$e->getMessage()}</p>");
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="CSS/dashboard.css">
  <style>
    /* Modal styles */
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
    <h1>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?>!</h1>
  </div>

  <div class="profile-info">
    <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($currentUser['phone']); ?></p>
    <img src="uploads/<?php echo htmlspecialchars($currentUser['profile_photo']); ?>" alt="Profile Photo" width="150">
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

      <?php if (!empty($currentUser['movies'])): ?>
        <?php foreach ($currentUser['movies'] as $movie): ?>
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

  <!-- Modal Structure -->
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