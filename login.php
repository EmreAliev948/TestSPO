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
        'user-read-recently-played'
    ],
];

$authorizeUrl = $session->getAuthorizeUrl($options);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="login.css">
    <style>
        
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login with Spotify</h2>
        <a href="<?php echo $authorizeUrl; ?>">
            <button class="login-button">Connect</button>
        </a>
    </div>
</body>
</html>