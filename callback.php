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
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT id FROM users WHERE spotify_id = :spotify_id");
    $stmt->bindParam(':spotify_id', $user->id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("INSERT INTO users (spotify_id, name) VALUES (:spotify_id, :name)");
        $stmt->bindParam(':spotify_id', $user->id);
        $stmt->bindParam(':name', $userName);
        $stmt->execute();
        echo "Inserted new user: $userName";
    } else {
        $stmt = $db->prepare("UPDATE users SET name = :name WHERE spotify_id = :spotify_id");
        $stmt->bindParam(':spotify_id', $user->id);
        $stmt->bindParam(':name', $userName);
        $stmt->execute();
        echo "Updated user: $userName";
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE spotify_id = :spotify_id");
    $stmt->bindParam(':spotify_id', $user->id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['userId'] = $row['id'];

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

header('Location: index.php');
die();
?>