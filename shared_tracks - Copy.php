<?php
$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($_SESSION['accessToken']);

$user = $api->me();
$userName = $topTracks[0]['username'] ?? 'Unknown User';
deletePlaylist($api, "Shared Top Tracks by " . $userName);

$playlist = $api->createPlaylist($user->id, [
    'name' => 'Shared Top Tracks by ' . $userName,
    'description' => 'Shared tracks playlist by ' . $userName,
    'public' => false,
]);

$trackUris = array_filter(array_map(function($track) {
    return isset($track['uri']) ? $track['uri'] : null;
}, $topTracks));

if (!empty($trackUris)) {
    $api->addPlaylistTracks($playlist->id, $trackUris);
}
$playlistId = $playlist->id;

// Add this right before the track-list div
if (!empty($topTracks)) {
    echo '<!-- Debug information -->';
    echo '<!-- First track structure: -->';
    echo '<!-- ' . print_r($topTracks[0], true) . ' -->';
}

// Add this near the top of the file, after $topTracks is set
if (!empty($topTracks)) {
    error_log('First track data: ' . print_r($topTracks[0], true));
}

// Add this near the top of the file
if (!empty($topTracks)) {
    error_log('Track data structure: ' . print_r($topTracks[0], true));
    
    // Debug output
    echo '<!-- Track data structure: -->';
    echo '<!-- ' . print_r($topTracks[0], true) . ' -->';
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="css/header.css">
    <title>Shared Tracks</title>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="header-text">
                <span class="music-note">♪</span>
                <h1>Shared Tracks</h1>
                <p>Check out these shared tracks</p>
            </div>
        </div>
    </div>

    <div class="nav-container">
        <div class="nav-content">
            <div class="nav-left">
                <a href="#" class="nav-link share-button">
                    <span class="nav-icon">↗</span>
                    Share
                </a>
                <a href="index.php" class="nav-link">
                    <span class="nav-icon">♪</span>
                    My Top Tracks
                </a>
                <a href="index.php?action=topcharts" class="nav-link">
                    <span class="nav-icon">☰</span>
                    Top Charts
                </a>
            </div>
            <div class="nav-right">
                <a href="logout.php" class="connect-spotify logout">Logout</a>
            </div>
        </div>
    </div>

    <div id="spotify-iframe"></div>

    <script src="https://open.spotify.com/embed-podcast/iframe-api/v1"></script>
    <script>
    function updatePlayer(uri) {
        const element = document.getElementById('spotify-iframe');
        if (element && window.IFrameAPI) {
            const options = {
                width: '100%',
                height: '352',
                uri: uri
            };
            IFrameAPI.createController(element, options, () => {});
        }
    }

    // Store IFrameAPI when it's ready
    window.onSpotifyIframeApiReady = (IFrameAPI) => {
        window.IFrameAPI = IFrameAPI;
        const element = document.getElementById('spotify-iframe');
        const options = {
            width: '100%',
            height: '352',
            uri: 'spotify:playlist:<?php echo $playlistId; ?>'
        };
        IFrameAPI.createController(element, options, () => {});
    };
    </script>

    <div class="track-list">
        <?php if (!empty($topTracks)): ?>
            <?php foreach ($topTracks as $track): ?>
                <div class="track-item" data-uri="<?php echo $track['uri'] ?? ''; ?>">
                    <?php 
                    // Simplified image handling based on your data structure
                    $imageUrl = $track['image_url'] ?? '';
                    $trackName = $track['name'] ?? 'Unknown Track';
                    $artistName = $track['artist'] ?? 'Unknown Artist';
                    $albumName = $track['album'] ?? 'Unknown Album';
                    ?>
                    
                    <div class="track-image-container">
                        <?php if ($imageUrl): ?>
                            <!-- Debug: Print the image URL -->
                            <!-- Image URL: <?php echo $imageUrl; ?> -->
                            <img class="track-image" src="<?php echo htmlspecialchars($imageUrl); ?>"
                                 alt="<?php echo htmlspecialchars($trackName); ?>"
                                 onclick="updatePlayer('<?php echo htmlspecialchars($track['uri'] ?? ''); ?>')">
                        <?php else: ?>
                            <div class="no-image">No Image Available</div>
                        <?php endif; ?>
                    </div>
                    
                    <h2><?php echo htmlspecialchars($trackName); ?></h2>
                    <p>By <?php echo htmlspecialchars($artistName); ?></p>
                    <p>Album: <?php echo htmlspecialchars($albumName); ?></p>
                    
                    <!-- Add play button -->
                    <button class="play-button" onclick="updatePlayer('<?php echo htmlspecialchars($track['uri'] ?? ''); ?>')">
                        Play Track
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="error-message">
                <p>No tracks available to display</p>
                <a href="index.php" class="nav-link">Return to My Top Tracks</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>