<?php
session_start();

// Get JSON data
$users = json_decode(file_get_contents('users.json'), true);

if (!isset($_SESSION['user_id']) || !isset($_POST['poster_id']) || !isset($_POST['movie_id'])) {
  echo "error";
  exit();
}

$userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));
if ($userIndex === false) {
  echo "error";
  exit();
}

$movies = &$users[$userIndex]['movies'];
$movieIndex = array_search($_POST['movie_id'], array_column($movies, 'id'));

if ($movieIndex === false) {
  echo "error";
  exit();
}

$movie = &$movies[$movieIndex];

// Find and remove the poster
foreach ($movie['posters'] as $key => $poster) {
  if ($poster['id'] === $_POST['poster_id']) {
    $posterPath = "uploads/" . $poster['image'];
    if (file_exists($posterPath)) {
      unlink($posterPath);
    }
    unset($movie['posters'][$key]);
    $movie['posters'] = array_values($movie['posters']);
    file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
    echo "success";
    exit();
  }
}

echo "error";
exit();
