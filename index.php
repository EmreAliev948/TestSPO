<?php
require 'private.php';
require 'vendor/autoload.php';
require 'functions.php';

session_start();

if (!isset($_SESSION['accessToken'])) {
    header('Location: login.php');
    exit;
}

$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($_SESSION['accessToken']);

try {
    if (isset($_GET['action']) && $_GET['action'] === 'topcharts') {
        $playlistId = createPopular($api);
        if ($playlistId) {
            include 'top_charts.php';
        } else {
            echo "<div class='error'>Unable to create community playlist</div>";
            echo "<p><a href='index.php'>Return to My Top Tracks</a></p>";
        }
    } else if (isset($_GET['id'])) {
        $shareId = $_GET['id'];
        $sharedTracks = displayShared($shareId);
        if ($sharedTracks) {
            $topTracks = $sharedTracks;
            include 'shared_tracks.php';
        } else {
            echo "Invalid share link";
        }
    } else {
        $topTracks = $api->getMyTop('tracks', [
            'limit' => 20,
            'time_range' => 'short_term'
        ]);
        $shareLink = storeShared($topTracks, $_SESSION['userId'], $_SESSION['spotifyId']);
        include 'top_tracks.php';
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    header('Location: login.php');
    exit;
}
?>