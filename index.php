<?php
require 'private.php';
require 'vendor/autoload.php';
require 'functions.php';

session_start();

if (isset($_GET['id'])) {
    $shareId = $_GET['id'];
    displaySharedTracks($shareId, $dbHost, $dbName, $dbUser, $dbPass); 

} else if (isset($_SESSION['accessToken'])) {
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken($_SESSION['accessToken']);

    try {
        $topTracks = $api->getMyTop('tracks');
        $shareLink = storeAndDisplayTopTracks($topTracks, $_SESSION['userId'], $_SESSION['spotifyId'], $dbHost, $dbName, $dbUser, $dbPass);
        include 'top_tracks.php';

    } catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }

} else {
    header('Location: login.php');
    exit;
}

?>