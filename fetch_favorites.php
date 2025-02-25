<?php
include_once "db_connection.php";

// Fetch favorite movies with multiple posters
$sql = "SELECT m.id, m.movie_name, m.rating, GROUP_CONCAT(p.image) AS images 
        FROM movies m 
        JOIN movie_posters p ON m.id = p.movie_id 
        GROUP BY m.id, m.movie_name, m.rating";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo '<div class="movie-card">';
    echo '<h3>' . htmlspecialchars($row["movie_name"]) . '</h3>';
    echo '<p>Rating: ' . htmlspecialchars($row["rating"]) . '/10</p>';

    // Split the concatenated image URLs into an array
    $images = explode(',', $row["images"]);
    echo '<div class="movie-posters">';
    foreach ($images as $image) {
      echo '<img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($row["movie_name"]) . '">';
    }
    echo '</div>';

    echo '</div>';
  }
} else {
  echo "<p>No favorite movies found.</p>";
}

$conn->close();
?>