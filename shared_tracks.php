<?php 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shared Top Tracks</title>
    <link rel="stylesheet" type="text/css" href="shared_tracks.css">
</head>
<body>
    <div class="top-bar">
        <a href="index.php">Back</a>
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>
    <h1>Shared Top Tracks by <?php echo $userName; ?></h1>
    <button id="add-all-button" class="add-button">Add All to Playlist</button>
    <div class="track-list">
        <?php foreach ($topTracks as $track): ?>
            <div class="track-item" data-uri="<?php echo $track['uri']; ?>">
                <?php if (isset($track['image_url'])): ?> 
                    <img class="track-image" src="<?php echo $track['image_url']; ?>" alt="<?php echo $track['name']; ?>"> 
                    <div class="track-overlay">
                        <button class="play-button">Play</button>
                    </div>
                <?php endif; ?>
                <h2><?php echo $track['name']; ?></h2>
                <p>By <?php echo $track['artist']; ?></p> 
                <p>Album: <?php echo $track['album']; ?></p> 
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://sdk.scdn.co/spotify-player.js"></script>
    <script>
        window.onSpotifyWebPlaybackSDKReady = () => {
            const token = '<?php echo $_SESSION['accessToken']; ?>';

            const player = new Spotify.Player({
                name: 'My Spotify Player',
                getOAuthToken: cb => { cb(token); }
            });

            player.addListener('initialization_error', ({ message }) => { });
            player.addListener('authentication_error', ({ message }) => {  });
            player.addListener('account_error', ({ message }) => {  });
            player.addListener('playback_error', ({ message }) => { });

            player.addListener('player_state_changed', state => { });

            player.addListener('ready', ({ device_id }) => {
                const trackItems = document.querySelectorAll('.track-item');
                trackItems.forEach(item => {
                    item.querySelector('.play-button').addEventListener('click', () => {
                        const trackUri = item.dataset.uri;
                        player._options.getOAuthToken(access_token => {
                            fetch(`https://api.spotify.com/v1/me/player/play?device_id=${device_id}`, { 
                                method: 'PUT',
                                body: JSON.stringify({ uris: [trackUri] }),
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': `Bearer ${access_token}`
                                },
                            }).catch(err => {
                            });
                        });
                    });
                });

                document.getElementById('add-all-button').addEventListener('click', () => {
                    const uris = Array.from(trackItems).map(item => item.dataset.uri);
                    player._options.getOAuthToken(access_token => {
                        fetch('https://api.spotify.com/v1/me/playlists', {
                            method: 'POST',
                            body: JSON.stringify({ name: 'Shared Top Tracks Playlist by <?php echo $userName; ?>' }),
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
                                body: JSON.stringify({ uris: uris }),
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': `Bearer ${access_token}`
                                },
                            })
                            .then(() => {
                                console.debug('Tracks successfully.');
                            })
                            .catch(err => {
                                console.debug('Failed playlist.', err);
                            });
                        })
                        .catch(err => {
                            console.debug('Failed playlist.', err);
                        });
                    });
                });
            });

            player.addListener('not_ready', ({ device_id }) => {  });

            player.connect().then(success => {
                if (!success) {
                    
                }
            });
        };
    </script>
</body>
</html>