<?php
$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($_SESSION['accessToken']);

$user = $api->me();
$isPremium = ($user->product === 'premium');
$shareLink = storeShared($topTracks, $_SESSION['userId'], $_SESSION['spotifyId']) ?? '#';
if (!$shareLink) {
    $shareLink = '#';
    error_log("Failed to generate share link for user: " . $_SESSION['userId']);
}

$pdo = getPDOConnection();
$playlistData = getPlaylist($_SESSION['userId'], $pdo);
$playlistId = $playlistData ? $playlistData['spotify_playlist_id'] : null;

if (!$playlistId) {
    $userPlaylists = $api->getUserPlaylists($user->id);
    $existingPlaylist = null;
    
    foreach ($userPlaylists->items as $playlist) {
        if ($playlist->name === 'My Top Tracks') {
            $existingPlaylist = $playlist;
            break;
        }
    }
    
    if ($existingPlaylist) {
        $playlistId = $existingPlaylist->id;
    } else {
        $playlist = $api->createPlaylist($user->id, [
            'name' => 'My Top Tracks',
            'description' => 'My personal top tracks playlist',
            'public' => false,
        ]);
        
        $trackUris = [];
        foreach ($topTracks->items as $track) {
            if (isset($track->uri)) {
                $trackUris[] = $track->uri;
            }
        }
        
        if (!empty($trackUris)) {
            $api->addPlaylistTracks($playlist->id, $trackUris);
        }
        
        $playlistId = $playlist->id;
    }
    
    savePlaylist($_SESSION['userId'], $playlistId, $pdo);
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="css/header.css">
    <title>My Top Tracks</title>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="header-text">
                <span class="music-note">♪</span>
                <h1>Top Music Charts</h1>
                <p>Discover your and your friends top tracks</p>
            </div>
        </div>
    </div>

    <div class="nav-container">
        <div class="nav-content">
            <div class="nav-left">
                <a href="#" onclick="showSharePopup('<?php echo $shareLink; ?>'); return false;" class="nav-link share-button">
                    <span class="nav-icon">↗</span>
                    Share
                </a>
                <a href="index.php" class="nav-link active">
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
    
    <div class="track-list">
        <?php foreach ($topTracks->items as $track): ?>
            <div class="track-item" data-uri="<?php echo $track->uri; ?>">
                <?php if (isset($track->album->images[0]->url)): ?>
                    <img class="track-image" src="<?php echo $track->album->images[0]->url; ?>"
                         alt="<?php echo $track->name; ?>"
                         <?php if (!$isPremium): ?>onclick="updatePlayer('<?php echo $track->uri; ?>')"<?php endif; ?>>
                <?php endif; ?>
                <h2><?php echo $track->name; ?></h2>
                <p>By <?php echo implode(', ', array_column($track->artists, 'name')); ?></p>
                <p>Album: <?php echo $track->album->name; ?></p>
                <?php if ($isPremium): ?>
                    <button class="play-button">Play</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="sharePopup" class="share-popup">
        <div class="share-popup-content">
            <h3>Share your playlist</h3>
            <div class="share-link-container">
                <input type="text" id="shareLink" readonly>
                <button onclick="copyShareLink()">Copy</button>
            </div>
            <button class="close-button" onclick="closeSharePopup()">Close</button>
        </div>
    </div>

    <script src="https://open.spotify.com/embed-podcast/iframe-api/v1"></script>
    <script>
    let controller;
    window.onSpotifyIframeApiReady = (IFrameAPI) => {
        const element = document.getElementById('spotify-iframe');
        const options = {
            width: '100%',
            height: '352',
            uri: 'spotify:playlist:<?php echo $playlistId; ?>'
        };
        const callback = (EmbedController) => {
            controller = EmbedController;
            document.querySelectorAll('.track-item').forEach(track => {
                track.addEventListener('click', () => {
                    const uri = track.getAttribute('data-uri');
                    controller.loadUri(uri);
                });
            });
        };
        IFrameAPI.createController(element, options, callback);
    };

    function updatePlayer(uri) {
        if (controller) {
            controller.loadUri(uri);
        }
    }
    </script>
    <script src="https://sdk.scdn.co/spotify-player.js"></script>
    <script>
        window.onSpotifyWebPlaybackSDKReady = () => {
            const token = '<?php echo isset($_SESSION['accessToken']) ? $_SESSION['accessToken'] : ''; ?>';

            if (!token) {
                alert('No access token available. Please log in again.');
                window.location.href = 'login.php';
                return;
            }

            const player = new Spotify.Player({
                name: 'Spotify Player',
                getOAuthToken: cb => {
                    cb(token);
                }
            });

            player.addListener('initialization_error', ({
                message
            }) => { });
            player.addListener('authentication_error', ({
                message
            }) => { });
            player.addListener('account_error', ({
                message
            }) => { });
            player.addListener('playback_error', ({
                message
            }) => { });

            player.addListener('player_state_changed', state => { });

            player.addListener('ready', ({
                device_id
            }) => {
                const trackItems = document.querySelectorAll('.track-item');
                trackItems.forEach(item => {
                    item.querySelector('.play-button').addEventListener('click', () => {
                        const trackUri = item.dataset.uri;
                        player._options.getOAuthToken(access_token => {
                            fetch(`https://api.spotify.com/v1/me/player/play?device_id=${device_id}`, {
                                method: 'PUT',
                                body: JSON.stringify({
                                    uris: [trackUri]
                                }),
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': `Bearer ${access_token}`
                                },
                            }).catch(err => { });
                        });
                    });
                });

                <?php if ($isPremium): ?>
                    document.getElementById('add-all-button').addEventListener('click', () => {
                        const uris = Array.from(trackItems).map(item => item.dataset.uri);
                        player._options.getOAuthToken(access_token => {
                            fetch('https://api.spotify.com/v1/me/playlists', {
                                method: 'POST',
                                body: JSON.stringify({
                                    name: 'My Top Tracks Playlist'
                                }),
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': `Bearer ${access_token}`
                                },
                            })
                                .then(response => response.json())
                                .then(data => {
                                    const playlistId = data.id;
                                    fetch(`https://api.spotify.com/v1/playlists/${playlistId}/tracks`, {
                                        method: 'POST',
                                        body: JSON.stringify({
                                            uris: uris
                                        }),
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Authorization': `Bearer ${access_token}`
                                        },
                                    })
                                        .then(() => {
                                            console.debug('Tracks successfully.');
                                        })
                                        .catch(err => {
                                            console.debug('Failed tracks.', err);
                                        });
                                })
                                .catch(err => {
                                    console.debug('Failed playlist.', err);
                                });
                        });
                    });
                <?php endif; ?>
            });

            player.addListener('not_ready', ({
                device_id
            }) => { });

            player.connect().then(success => {
                if (!success) { }
            });
        };
    </script>
    <script>
    function showSharePopup(link) {
        document.getElementById('shareLink').value = link;
        document.getElementById('sharePopup').classList.add('active');
    }

    function closeSharePopup() {
        document.getElementById('sharePopup').classList.remove('active');
    }

    function copyShareLink() {
        const shareLink = document.getElementById('shareLink');
        shareLink.select();
        document.execCommand('copy');
        
        const copyButton = event.target;
        const originalText = copyButton.textContent;
        copyButton.textContent = 'Copied!';
        setTimeout(() => {
            copyButton.textContent = originalText;
        }, 2000);
    }
    </script>
</body>

</html>