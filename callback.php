<?php

require 'vendor/autoload.php';
require 'private.php';

session_start();

$session = new SpotifyWebAPI\Session(
    $clientId,
    $clientSecret,
    $redirectUri
);

$session->requestAccessToken($_GET['code']);
$accessToken = $session->getAccessToken();
$refreshToken = $session->getRefreshToken();

$_SESSION['accessToken'] = $accessToken;
$_SESSION['refreshToken'] = $refreshToken;

$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($accessToken);

$user = $api->me();
$_SESSION['userId'] = $user->id;
$_SESSION['spotifyId'] = $user->id;
$userName = $user->display_name;

try {
    $db = getPDOConnection();
    $stmt = $db->prepare("INSERT INTO users (spotify_id, name) VALUES (:spotify_id, :name) ON DUPLICATE KEY UPDATE name = :name");
    $stmt->execute([
        ':spotify_id' => $user->id,
        ':name' => $userName
    ]);
    $stmt = $db->prepare("SELECT id FROM users WHERE spotify_id = :spotify_id");
    $stmt->execute([':spotify_id' => $user->id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $_SESSION['userId'] = $row['id'];
        header('Location: index.php');
        exit();
    } else {
        throw new Exception("Failed user ID");
    }

} catch (Exception $e) {
    error_log("ErrOR " . $e->getMessage());
    header('Location: login.php?error=database');
    exit();
}
?>