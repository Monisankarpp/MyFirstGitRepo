<?php
session_start();

$movieId = $_GET['movie_id'];

$users = json_decode(file_get_contents('users.json'), true);
$userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));

$movieIndex = array_search($movieId, array_column($users[$userIndex]['movies'], 'id'));
$movie = $users[$userIndex]['movies'][$movieIndex];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $movieName = trim($_POST['movie_name']);
  $rating = trim($_POST['rating']);

  // Update poster if uploaded
  if (!empty($_FILES['poster']['name'])) {
    $uploadMovie = 'uploads/';
    $posterName = $_FILES['poster']['name'];
    $posterPath = $uploadMovie . $posterName;
    move_uploaded_file($_FILES['poster']['tmp_name'], $posterPath);
    $movie['poster'] = $posterName;
  }
  $movie['name'] = $movieName;
  $movie['rating'] = $rating;
  $users[$userIndex]['movies'][$movieIndex] = $movie;
  file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
  header("Location: dashboard.php");
  exit();
}
?>

<!-- HTML Form -->
<!-- HTML Start -->
<!DOCTYPE html>
<html>

<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="CSS/add-movie.css">
</head>

<body>
  <h2>Edit Movie</h2>
  <form action="" method="POST" enctype="multipart/form-data">
    <label>Movie Name:</label>
    <input type="text" name="movie_name" value="<?php echo htmlspecialchars($movie['name']); ?>" required><br>

    <label>Rating:</label>
    <input type="text" name="rating" value="<?php echo htmlspecialchars($movie['rating']); ?>" required><br>

    <label>Change Poster:</label>
    <input type="file" name="poster"><br>

    <button type="submit">Update Movie</button>
  </form>

  <a href="dashboard.php">Back to Dashboard</a>
</body>

</html>