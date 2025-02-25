<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="CSS/style.css">
  <title>Movie Review Home</title>
</head>

<body>
  <nav>
    <div class="logo">MovieReview</div>
    <ul class="menu">
      <li><a href="#">Home</a></li>
      <li><a href="#">Movies</a></li>
      <li><a href="review.php">Reviews</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
    <div class="auth-buttons">
      <button><a href="login.php">Login</a></button>
      <button><a href="signup.php">Signup</a></button>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
      <h1>Discover and Review Your Favorite Movies</h1>
      <p>Explore top-rated movies, share reviews, and rate your favorites.</p>
    </div>
  </section>

  <h2 class="section-title">Popular Movies</h2>
  <section class="movie-grid" id="popular-movies">
    <?php include 'fetch_popular.php'; ?>
  </section>

  <h2 class="section-title">Your Favorites</h2>
  <section class="movie-grid" id="favorites">
    <?php
    include 'db_connection.php';

    $sql = "SELECT m.id, m.movie_name, m.rating, GROUP_CONCAT(p.image) AS images 
            FROM movies m 
            JOIN movie_posters p ON m.id = p.movie_id 
            GROUP BY m.id, m.movie_name, m.rating";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $movieId = $row["id"];
        $movieName = htmlspecialchars($row["movie_name"]);
        $rating = htmlspecialchars($row["rating"]);
        $images = explode(',', $row["images"]); // Convert image list to an array
        ?>

        <div class="movie-card">
          <div id="carousel-<?php echo $movieId; ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <?php
              foreach ($images as $index => $image) {
                $activeClass = ($index === 0) ? 'active' : '';
                echo '<div class="carousel-item ' . $activeClass . '">';
                echo '<img src="' . htmlspecialchars($image) . '" class="d-block w-100" alt="' . $movieName . '">';
                echo '</div>';
              }
              ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $movieId; ?>"
              data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $movieId; ?>"
              data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
          <h3><?php echo $movieName; ?></h3>
          <p>Rating: <?php echo $rating; ?>/10</p>
        </div>

        <?php
      }
    } else {
      echo "<p>No favorite movies found.</p>";
    }

    $conn->close();
    ?>
  </section>

  <footer>
    <p>&copy; 2025 MovieReview. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>