<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// passe de s -> m:s
function formatDuration(int $seconds): string {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $seconds);
}

//BDD
try {
    $db = new DatabaseManager(
            dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
            username: 'lowify',
            password: 'lowifypassword'
    );
} catch (PDOException $ex) {
    header("Location: display_error.php?message=Erreur connexion BDD");
    exit;
}

// Récup de la recherche
$searchQuery = "";
if (isset($_GET['query'])) {
    $searchQuery = trim($_GET['query']);
}

// Initialisation des résultats
$artists = [];
$albums = [];
$songs = [];

if (!empty($searchQuery)) {
    // au cas ou
    $safeSearch = addslashes($searchQuery);

    // Artistes
    try {
        $sqlArtist = "SELECT id, name, cover FROM artist WHERE name LIKE '%$safeSearch%'";
        $artists = $db->executeQuery($sqlArtist);
    } catch (PDOException $e) { /* Ignorer ou logger */ }

    // Albums
    try {
        $sqlAlbum = "SELECT al.id, al.name, al.cover, al.release_date, ar.name as artist_name 
                     FROM album al 
                     JOIN artist ar ON al.artist_id = ar.id 
                     WHERE al.name LIKE '%$safeSearch%'";
        $albums = $db->executeQuery($sqlAlbum);
    } catch (PDOException $e) { /* Ignorer ou logger */ }

    // Chansons
    try {
        $sqlSong = "SELECT s.name, s.duration, s.note, al.name as album_name, ar.name as artist_name 
                    FROM song s 
                    JOIN album al ON s.album_id = al.id 
                    JOIN artist ar ON al.artist_id = ar.id
                    WHERE s.name LIKE '%$safeSearch%'";
        $songs = $db->executeQuery($sqlSong);
    } catch (PDOException $e) { /* Ignorer ou logger */ }
}


// HTML ARTISTE
$artistsHtml = "";
if (count($artists) > 0) {
    $artistsHtml .= '<h3 class="mb-3 fw-bold text-white">Artistes</h3><div class="row mb-5">';
    foreach ($artists as $artist) {
        $id = $artist['id'];
        $name = htmlspecialchars($artist['name']);
        $cover = htmlspecialchars($artist['cover']);

        $artistsHtml .= <<<HTML
        <div class="col-lg-3 col-md-4 col-6 mb-3">
            <a href="artist.php?id=$id" class="text-decoration-none text-white">
                <div class="card bg-dark border-secondary hover-scale text-center p-3 h-100">
                    <div class="d-flex justify-content-center mb-3">
                        <img src="$cover" alt="$name" class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <h5 class="card-title text-truncate">$name</h5>
                    <span class="badge bg-secondary rounded-pill">Artiste</span>
                </div>
            </a>
        </div>
HTML;
    }
    $artistsHtml .= '</div>';
}

// HTML ALBUMS
$albumsHtml = "";
if (count($albums) > 0) {
    $albumsHtml .= '<h3 class="mb-3 fw-bold text-white">Albums</h3><div class="row mb-5">';
    foreach ($albums as $album) {
        $id = $album['id'];
        $name = htmlspecialchars($album['name']);
        $cover = htmlspecialchars($album['cover']);
        $artistName = htmlspecialchars($album['artist_name']);
        $year = substr($album['release_date'], 0, 4);

        $albumsHtml .= <<<HTML
        <div class="col-lg-2 col-md-3 col-6 mb-3">
            <a href="album.php?id=$id" class="text-decoration-none text-white">
                <div class="card bg-dark border-0 hover-bg h-100">
                    <img src="$cover" class="card-img-top rounded shadow mb-2" alt="$name" style="aspect-ratio: 1/1; object-fit: cover;">
                    <div class="card-body p-0">
                        <h6 class="card-title text-truncate mb-1 fw-bold">$name</h6>
                        <p class="card-text small text-secondary mb-0">$year • $artistName</p>
                    </div>
                </div>
            </a>
        </div>
HTML;
    }
    $albumsHtml .= '</div>';
}

// HTML CHANSONS
$songsHtml = "";
if (count($songs) > 0) {
    $songsHtml .= '<h3 class="mb-3 fw-bold text-white">Titres</h3><div class="list-group list-group-flush mb-5">';
    foreach ($songs as $song) {
        $name = htmlspecialchars($song['name']);
        $duration = formatDuration((int)$song['duration']);
        $note = htmlspecialchars($song['note']);
        $albumName = htmlspecialchars($song['album_name']);
        $artistName = htmlspecialchars($song['artist_name']);

        $badgeColor = ($song['note'] >= 15) ? 'text-bg-success' : 'text-bg-primary';

        $songsHtml .= <<<HTML
        <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center hover-bg">
            <div class="d-flex align-items-center overflow-hidden">
                <i class="bi bi-music-note-beamed me-3 text-secondary"></i>
                <div class="text-truncate">
                    <strong class="d-block text-truncate">$name</strong>
                    <small class="text-secondary">$artistName • $albumName</small>
                </div>
            </div>
            <div class="d-flex align-items-center ms-3" style="min-width: 120px; justify-content: flex-end;">
                <span class="me-3 text-muted small">$duration</span>
                <span class="badge $badgeColor rounded-pill">$note / 5</span>
            </div>
        </div>
HTML;
    }
    $songsHtml .= '</div>';
}

// si il ne trouve pas
$noResultsHtml = "";
if (empty($artists) && empty($albums) && empty($songs) && !empty($searchQuery)) {
    $noResultsHtml = <<<HTML
    <div class="text-center py-5">
        <i class="bi bi-search fs-1 text-secondary mb-3"></i>
        <h3 class="text-white">Aucun résultat trouvé pour "$searchQuery"</h3>
        <p class="text-secondary">Essayez de vérifier l'orthographe ou d'utiliser d'autres mots-clés.</p>
    </div>
HTML;
} elseif (empty($searchQuery)) {
    $noResultsHtml = <<<HTML
    <div class="text-center py-5">
        <i class="bi bi-search fs-1 text-secondary mb-3"></i>
        <h3 class="text-white">Lancez une recherche</h3>
        <p class="text-secondary">Tapez le nom d'un artiste, d'un album ou d'une chanson ci-dessus.</p>
    </div>
HTML;
}


$customStyles = <<<CSS
<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.05); }
    .hover-bg { transition: background-color 0.2s; padding: 8px; border-radius: 6px; }
    .hover-bg:hover { background-color: #2a2a2a !important; }
</style>
CSS;

$mainHtml = <<<HTML
$customStyles
<div class="container bg-dark text-white p-4" style="min-height: 100vh;">
    
    <!-- Header Recherche -->
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-light btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Retour à l'accueil
        </a>
        <h1 class="display-5 fw-bold mb-4">Recherche</h1>
        
        <form action="search.php" method="GET" class="d-flex mb-5" role="search">
            <input class="form-control form-control-lg me-2 rounded-pill border-0 bg-secondary text-white" 
                   type="text" 
                   name="query" 
                   value="$searchQuery" 
                   placeholder="Que souhaitez-vous écouter ?" 
                   aria-label="Search">
            <button class="btn btn-success btn-lg rounded-pill px-4" type="submit">Rechercher</button>
        </form>
    </div>

    <!-- Résultats -->
    $artistsHtml
    $albumsHtml
    $songsHtml
    $noResultsHtml

</div>
HTML;

$page = new HTMLPage("Recherche : $searchQuery - Lowify");
$page->setupBootstrap([
        "class" => "bg-dark text-white",
        "data-bs-theme" => "dark"
]);
$page->addContent($mainHtml);
echo $page->render();