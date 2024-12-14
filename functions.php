<?php



function displaySharedTracks($shareId, $dbHost, $dbName, $dbUser, $dbPass) {
    try {
        $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT user_id FROM shared_tracks WHERE share_id = :share_id");
        $stmt->bindParam(':share_id', $shareId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $row['user_id'];

            $stmt = $db->prepare("SELECT * FROM top_tracks WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $topTracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT name FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $userName = $user['name'];

            include 'shared_tracks.php';

        } else {
            echo "Invalid share ID.";
        }

    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}

function storeAndDisplayTopTracks($topTracks, $userId, $spotifyId, $dbHost, $dbName, $dbUser, $dbPass) {
    try {
        $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT id FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO users (id, spotify_id) VALUES (:user_id, :spotify_id)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':spotify_id', $spotifyId);
            $stmt->execute();
        }

        $stmt = $db->prepare("DELETE FROM top_tracks WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $stmt = $db->prepare("INSERT INTO top_tracks (user_id, name, artist, album, image_url, uri) VALUES (:user_id, :name, :artist, :album, :image_url, :uri)");
        foreach ($topTracks->items as $track) {
            $artistNames = implode(', ', array_column($track->artists, 'name'));
            $imageUrl = $track->album->images[0]->url; 

            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':name', $track->name);
            $stmt->bindParam(':artist', $artistNames);
            $stmt->bindParam(':album', $track->album->name);
            $stmt->bindParam(':image_url', $imageUrl); 
            $stmt->bindParam(':uri', $track->uri);
            $stmt->execute();
        }

        $shareId = uniqid();

        $stmt = $db->prepare("INSERT INTO shared_tracks (user_id, share_id) VALUES (:user_id, :share_id)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':share_id', $shareId);
        $stmt->execute();

        $shareLink = "index.php?id=" . $shareId;

        return $shareLink;

    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}

function SpotifyEmbeded($trackUri) {
    $trackId = str_replace('spotify:track:', '', $trackUri);
    
    $embedUrl = "https://open.spotify.com/embed/track/" . $trackId;
    
    return $embedUrl;
}

?>