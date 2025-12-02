<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// accede a la database
try {
    $db = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: 'lowify',
        password: 'lowifypassword'
    );
} catch (PDOException $ex) {
    echo "Erreur connexion BDD : " . $ex->getMessage();
    exit;
}
//recup artist dans la db
$allArtists = [];
try {
    $allArtists = $db->executeQuery("SELECT id, name, cover FROM artist");
} catch (PDOException $ex) {
    echo "Erreur requête artistes : " . $ex->getMessage();
    exit;
}
//recup album dans la db
$allAlbums = [];
try {
    $allAlbums = $db->executeQuery("
        SELECT a.id, a.name, a.cover, a.release_date, artist.name as artist_name 
        FROM album a
        JOIN artist ON a.artist_id = artist.id
        ORDER BY a.release_date DESC
    ");
} catch (PDOException $ex) {
    echo "Erreur requête albums : " . $ex->getMessage();
    exit;
}

// HTML pour artist
$artistsAsHTML = "";
$iterator = 0;

if (count($allArtists) > 0) {
    $artistsAsHTML .= '<div class="row mb-4">';
    foreach ($allArtists as $artist) {
        $aName = htmlspecialchars($artist['name']);
        $aId = $artist['id'];
        $aCover = htmlspecialchars($artist['cover']);

        $artistsAsHTML .= <<<HTML
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="artist.php?id=$aId" class="text-decoration-none text-white">
                <div class="card h-100 bg-dark text-white border-secondary shadow-sm hover-scale">
                    <div class="p-4 text-center">
                        <img src="$aCover" class="card-img-top rounded-circle shadow" alt="$aName" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">$aName</h5>
                        <p class="card-text text-secondary small">Artiste</p>
                    </div>
                </div>
            </a>
        </div>
HTML;
    }
    $artistsAsHTML .= '</div>'; // Fermeture row
}
// HTML pour album
$albumsAsHTML = "";

if (count($allAlbums) > 0) {
    $albumsAsHTML .= '<div class="row mb-4">';
    foreach ($allAlbums as $album) {
        $albName = htmlspecialchars($album['name']);
        $albId = $album['id'];
        $albCover = htmlspecialchars($album['cover']);
        $albArtist = htmlspecialchars($album['artist_name']);
        $year = substr($album['release_date'], 0, 4);

        $albumsAsHTML .= <<<HTML
        <div class="col-lg-2 col-md-4 col-6 mb-4">
            <a href="album.php?id=$albId" class="text-decoration-none text-white">
                <div class="card h-100 bg-dark text-white border-0 shadow-sm hover-bg">
                    <img src="$albCover" class="card-img-top rounded shadow" alt="$albName" style="aspect-ratio: 1/1; object-fit: cover;">
                    <div class="card-body px-0 pt-3">
                        <h6 class="card-title text-truncate mb-1" title="$albName">$albName</h6>
                        <p class="card-text text-secondary small mb-0">$year • $albArtist</p>
                    </div>
                </div>
            </a>
        </div>
HTML;
    }
    $albumsAsHTML .= '</div>';
}
// HTML final
$html = <<<HTML
<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.05); border-color: white !important; }
    
    .hover-bg { transition: background-color 0.2s; padding: 10px; border-radius: 8px; }
    .hover-bg:hover { background-color: #282828 !important; }
</style>
<div class="container bg-dark text-white p-4" style="min-height: 100vh;">
    
    <!-- Navigation -->
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à l'accueil
        </a>
    </div>

    <!-- Section Artistes -->
    <h2 class="mb-4 fw-bold">Artistes</h2>
    $artistsAsHTML
    
    <hr class="border-secondary my-5">

    <!-- Section Albums -->
    <h2 class="mb-4 fw-bold">Albums populaires</h2>
    $albumsAsHTML

</div>
HTML;

echo (new HTMLPage(title: "Catalogue - Lowify"))
    ->setupBootstrap([
        "class" => "bg-dark text-white",
        "data-bs-theme" => "dark"
    ])
    ->setupNavigationTransition()
    ->addContent($html)
    ->render();