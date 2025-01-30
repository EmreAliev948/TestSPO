<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/header.css">
    <title>Top Charts - Spotify Top Tracks</title>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-text">
                <span class="music-note">♪</span>
                <h1>Global Top Charts</h1>
                <p>Discover our user top music charts</p>
            </div>
        </div>
    </div>

    <div class="nav-container">
        <div class="nav-content">
            <div class="nav-left">
                <a href="index.php" class="nav-link">
                    <span class="nav-icon">♪</span>
                    My Top Tracks
                </a>
                <a href="index.php?action=topcharts" class="nav-link active">
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

    window.onSpotifyIframeApiReady = (IFrameAPI) => {
        window.IFrameAPI = IFrameAPI;
        const element = document.getElementById('spotify-iframe');
        const options = {
            width: '100%',
            height: '352',
            uri: 'spotify:playlist:<?php echo $playlistId ?? ''; ?>'
        };
        IFrameAPI.createController(element, options, () => {});
    };
    </script>
</body>
</html>
