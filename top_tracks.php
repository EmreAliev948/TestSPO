<?php
$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($_SESSION['accessToken']);

$user = $api->me();
$isPremium = ($user->product === 'premium');
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="shared_tracks.css">
    <title>My Top Tracks</title>
</head>

<body>
    <div class="top-bar">
        <a href="<?php echo $shareLink; ?>">Share</a>
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>
    <h1>Top Tracks</h1>
    <?php if ($isPremium): ?>
    <button id="add-all-button" class="add-button">Add All to Playlist</button>
    <?php else: ?>
    <p>You need a Spotify Premium account to add tracks to a playlist.</p>
    <?php endif; ?>
    <div class="track-list">
        <?php foreach ($topTracks->items as $track): ?>
        <div class="track-item" data-uri="<?php echo $track->uri; ?>">
            <?php if (isset($track->album->images[0]->url)): ?>
            <img class="track-image" src="<?php echo $track->album->images[0]->url; ?>"
                alt="<?php echo $track->name; ?>">
            <?php endif; ?>
            <h2><?php echo $track->name; ?></h2>
            <p>By <?php echo implode(', ', array_column($track->artists, 'name')); ?></p>
            <p>Album: <?php echo $track->album->name; ?></p>
            <iframe src="<?php echo SpotifyEmbeded($track->uri); ?>" width="300" height="80" frameborder="0"
                allowtransparency="true" allow="encrypted-media"></iframe>
        </div>
        <?php endforeach; ?>
    </div>

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
            name: 'My Spotify Player',
            getOAuthToken: cb => {
                cb(token);
            }
        });

        player.addListener('initialization_error', ({
            message
        }) => {});
        player.addListener('authentication_error', ({
            message
        }) => {});
        player.addListener('account_error', ({
            message
        }) => {});
        player.addListener('playback_error', ({
            message
        }) => {});

        player.addListener('player_state_changed', state => {});

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
                        }).catch(err => {});
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
        }) => {});

        player.connect().then(success => {
            if (!success) {}
        });
    };
    </script>
</body>

</html>