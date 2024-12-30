<?php
require 'private.php';
require 'vendor/autoload.php';
require 'functions.php';

session_start();

if (isset($_GET['id'])) {
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
</body>
</html>