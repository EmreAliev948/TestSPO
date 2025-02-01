A PHP web application that allows users to view their Spotify top tracks, share playlists, and discover community music charts.

Core Features:

	Spotify Authentication: Uses OAuth2 for secure Spotify login via login.php

	Personal Top Tracks: Displays user's most played tracks from Spotify

	Playlist Sharing: Generates shareable links for playlists

	Community Charts: Aggregates top tracks from all users

	Embedded Player: Integrates Spotify's Web Playback SDK


How to run the repo.
1. Install XAMPP and Composer
https://getcomposer.org/
https://www.apachefriends.org/
2. Start XAMPP, create a database named spotifydb in phpMyAdmin, copy the information from the attached files into the database.
3. Clone the git repo to \xampp\htdocs
3. Type composer require jwilsson/spotify-web-api-php in the TestSPO folder in the terminal.
4. Download the attached private.php file and put it in the folder.
5. If you want to make an application at developers.spotify.com or tell me to add an email with an email if you don't want to bother.
6. Go to http://localhost/testSPO/login.php and it should work
