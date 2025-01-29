<?php

require 'vendor/autoload.php';
require 'private.php';

session_start();

$session = new SpotifyWebAPI\Session(
    $clientId,
    $clientSecret,
    $redirectUri
);

$options = [
    'scope' => [
        'ugc-image-upload',
        'user-read-playback-state',
        'user-modify-playback-state',
        'user-read-currently-playing',
        'streaming',
        'user-read-private',
        'playlist-read-collaborative',
        'playlist-modify-public',
        'playlist-read-private',
        'playlist-modify-private',
        'user-library-modify',
        'user-library-read',
        'user-read-playback-position',
        'user-top-read',
        'user-read-recently-played',
        'app-remote-control',
        'user-follow-modify',
        'user-follow-read',
        'user-read-email'
    ],
];

$authorizeUrl = $session->getAuthorizeUrl($options);

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="css/header.css">
    <title>Login - Spotify Top Tracks</title>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="header-text">
                <span class="music-note">♪</span>
                <h1>Welcome to Top Music Charts</h1>
                <p>Connect with Spotify to discover and share your favorite tracks</p>
            </div>
        </div>
    </div>

    <div class="nav-container">
        <div class="nav-content">
            <div class="nav-left">
                <span class="nav-link disabled">
                    <span class="nav-icon">♪</span>
                    My Top Tracks
                </span>
                <span class="nav-link disabled">
                    <span class="nav-icon">☰</span>
                    Top Charts
                </span>
            </div>
        </div>
    </div>

    <div class="login-container">
        <div class="login-note">♪</div>
        <h2>Get Started with Top Music Charts</h2>
        <p>Connect your Spotify account to discover your top tracks, explore our user top chart, and share your music taste with friends.</p>
        <a href="<?php echo $authorizeUrl; ?>" class="connect-spotify large">
            Connect with Spotify
        </a>
    </div>
</body>

</html>