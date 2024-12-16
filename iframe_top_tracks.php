<?php
require 'vendor/autoload.php';
/*САМО ЗА ТЕСТ ТРЯБВА ДА СЕ ДОБАВИ КЪМ SHARED_TRACKS.PHP И TOP_TRACKS*/
session_start(); 

if (!isset($_SESSION['accessToken'])) {
    echo "No access token available. Please log in again.";
    exit;
}

$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($_SESSION['accessToken']);

$topTracks = $api->getMyTop('tracks', ['limit' => 10]);

$userId = $api->me()->id;
$playlist = $api->createPlaylist($userId, [
    'name' => 'Top 10 Tracks',
    'description' => 'Top 10 tracks',
    'public' => false,
]);

$trackUris = array_map(function($track) {
    return $track->uri;
}, $topTracks->items);
$api->addPlaylistTracks($playlist->id, $trackUris);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Top Tracks</title>
    <link rel="stylesheet" type="text/css" href="shared_tracks.css">
</head>

<body>
    <div class="top-bar">
        <a href="index.php">Back</a>
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>
    <h1>Top Tracks</h1>
    <div id="iframe"></div>
    <div class="track-list">
        <?php foreach ($topTracks->items as $track): ?>
        <div class="track-item">
            <?php if (isset($track->album->images[0]->url)): ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://open.spotify.com/embed-podcast/iframe-api/v1"></script>
    <script>
    window.onSpotifyIframeApiReady = (IFrameAPI) => {
        const element = document.getElementById('iframe');
        const options = {
            width: '100%',
            height: '380',
            uri: 'spotify:playlist:<?php echo $playlist->id; ?>'
        };
        const callback = (EmbedController) => {
            document.querySelectorAll('.track-item').forEach(
                trackItem => {
                    trackItem.addEventListener('click', () => {
                        EmbedController.loadUri(trackItem.dataset.spotifyId);
                    });
                });
        };
        IFrameAPI.createController(element, options, callback);
    };
    </script>
</body>

</html>
