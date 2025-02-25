<?php
include_once "db_connection.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Movie Gallery</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

  <div class="container mt-4">
    <h2 class="text-center">Favorite Movies</h2>

    <div class="row">
      <?php
      // Fetch movies with multiple posters
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
          $images = explode(',', $row["images"]); // Convert comma-separated images into an array
          ?>

          <div class="col-md-6 mb-4">
            <div class="card">
              <div id="carousel-<?php echo $movieId; ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                  <?php
                  foreach ($images as $index => $image) {
                    $activeClass = ($index === 0) ? 'active' : ''; // First image should be active
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
              <div class="card-body text-center">
                <h5 class="card-title"><?php echo $movieName; ?></h5>
                <p class="card-text">Rating: <?php echo $rating; ?>/10</p>
              </div>
            </div>
          </div>

          <?php
        }
      } else {
        echo "<p class='text-center'>No favorite movies found.</p>";
      }

      $conn->close();
      ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>