<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$users = json_decode(file_get_contents('users.json'), true);
$currentUser = null;

foreach ($users as $user) {
  if ($user['id'] == $_SESSION['user_id']) {
    $currentUser = $user;
    break;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="CSS/dashboard.css">
</head>

<body>

  <div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($currentUser['username']); ?>!</h1>
  </div>

  <div class="profile-info">
    <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($currentUser['phone']); ?></p>
    <img src="uploads/<?php echo htmlspecialchars($currentUser['profile_photo']); ?>" alt="Profile Photo">
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
        <th>Poster</th>
        <th>Movie Name</th>
        <th>Rating</th>
        <th>Action</th>
      </tr>
      <?php foreach ($currentUser['movies'] as $movie): ?>
        <tr>
          <td><img src="uploads/<?php echo htmlspecialchars($movie['poster']); ?>" width="200" height="150"></td>
          <td><?php echo htmlspecialchars($movie['name']); ?></td>
          <td><?php echo htmlspecialchars($movie['rating']); ?></td>
          <td>
            <a class="action-btn" href="edit_movie.php?id=<?php echo $movie['id']; ?>">Edit</a>

          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

</body>

</html>