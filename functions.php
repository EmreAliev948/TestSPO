<?php
function storeShared($topTracks, $userId, $spotifyId)
{
    try {
        $db = getPDOConnection();
        $api = new SpotifyWebAPI\SpotifyWebAPI();
        $api->setAccessToken($_SESSION['accessToken']);

        $playlist = $api->createPlaylist($spotifyId, [
            'name' => 'Top Tracks Playlist',
            'description' => 'My top tracks playlist',
            'public' => false,
        ]);

        $trackUris = array_map(function ($track) {
            return $track->uri;
        }, $topTracks->items);
        $api->addPlaylistTracks($playlist->id, $trackUris);

        $stmt = $db->prepare("DELETE FROM top_tracks WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stmt = $db->prepare("INSERT INTO top_tracks (user_id, name, artist, album, image_url, uri, playlist_id) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($topTracks->items as $track) {
            $stmt->execute([
                $userId,
                $track->name,
                implode(', ', array_column($track->artists, 'name')),
                $track->album->name,
                $track->album->images[0]->url,
                $track->uri,
                $playlist->id
            ]);
        }

        $shareId = uniqid();
        $stmt = $db->prepare("INSERT INTO shared_tracks (user_id, share_id, playlist_id) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $shareId, $playlist->id]);

        return "index.php?id=" . $shareId;

    } catch (PDOException $e) {
        error_log("Store shared error: " . $e->getMessage());
        return false;
    }
}

function displayShared($shareId)
{
    try {
        $db = getPDOConnection();

        $stmt = $db->prepare("
            SELECT t.*, u.name as username, s.playlist_id 
            FROM shared_tracks s
            JOIN top_tracks t ON s.user_id = t.user_id 
            JOIN users u ON s.user_id = u.id
            WHERE s.share_id = ?
        ");
        $stmt->execute([$shareId]);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll();
        }
        return false;

    } catch (PDOException $e) {
        error_log("Display shared error: " . $e->getMessage());
        return false;
    }
}

function SpotifyEmbeded($trackUri)
{
    $trackId = str_replace('spotify:track:', '', $trackUri);

    $embedUrl = "https://open.spotify.com/embed/track/" . $trackId;

    return $embedUrl;
}

function savePlaylist($userId, $playlistId, $pdo)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO playlists (user_id, spotify_playlist_id, created_at) VALUES (:user_id, :playlist_id, NOW())");
        return $stmt->execute([
            ':user_id' => $userId,
            ':playlist_id' => $playlistId
        ]);
    } catch (PDOException $e) {
        error_log("Error saving playlist: " . $e->getMessage());
        return false;
    }
}

function getPlaylist($userId, $pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT spotify_playlist_id FROM playlists WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting playlist: " . $e->getMessage());
        return false;
    }
}

?>