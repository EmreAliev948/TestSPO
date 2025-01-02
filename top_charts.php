<!DOCTYPE html>
<html>
<head>
    <title>Community Top Charts</title>
    <link rel="stylesheet" type="text/css" href="/css/shared_tracks.css">
</head>
<body>
    <div class="container">
        <h1>Community Top Charts</h1>
        <div class="playlist">
            <iframe 
                src="https://open.spotify.com/embed/playlist/<?php echo $playlistId; ?>" 
                width="100%" 
                height="800" 
                frameborder="0" 
                allowtransparency="true" 
                allow="encrypted-media">
            </iframe>
        </div>
    </div>
</body>
</html>
