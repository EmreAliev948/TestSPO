<?php
require 'private.php';
require 'vendor/autoload.php';
require 'functions.php';

session_start();

if (isset($_GET['action']) && $_GET['action'] === 'topcharts') {
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    if (!isset($_SESSION['accessToken'])) {
        header('Location: login.php');
        exit;
    }
    $api->setAccessToken($_SESSION['accessToken']);
    $playlistId = createPopular($api);
    
    if ($playlistId) {
        include 'top_charts.php';
        exit;
    } else {
        echo "<div class='error'>Unable to create community playlist</div>";
        echo "<p><a href='index.php'>Return to My Top Tracks</a></p>";
        exit;
    }
} else if (isset($_GET['id'])) {
    $shareId = $_GET['id'];
    $sharedTracks = displayShared($shareId);
    
    if ($sharedTracks) {
        $topTracks = $sharedTracks;
        include 'shared_tracks.php';
    } else {
        echo "Invalid share link";
        exit;
    }
} else if (isset($_SESSION['accessToken'])) {
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken($_SESSION['accessToken']);

    try {
        $topTracks = $api->getMyTop('tracks', [
            'limit' => 20,
            'time_range' => 'short_term'
        ]);
        $shareLink = storeShared($topTracks, $_SESSION['userId'], $_SESSION['spotifyId']);
        include 'top_tracks.php';
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Spotify Top Tracks</title>
    <link rel="stylesheet" type="text/css" href="/css/shared_tracks.css">
</head>
<body>
    <div class="nav-buttons">
        <a href="index.php" class="nav-button">My Top Tracks</a>
        <a href="index.php?action=topcharts" class="nav-button">TOP CHARTS</a>
        <a href="logout.php" class="nav-button">Logout</a>
    </div>
</body>
</html>