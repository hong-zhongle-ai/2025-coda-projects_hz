<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// Fonction pour formater la durée (secondes -> mm:ss)
function formatDuration(int $seconds): string {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $seconds);
}
// au cas ou il n'y a rien
if (empty($_GET['id'])) {
    header("Location: display_error.php?message=Identifiant d'album manquant");
    exit;
}
// // recup les info grace au $_GET
$albumId = (int) $_GET['id'];

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
// recup album dans la db
try {
    $sql = "SELECT id, name, cover, release_date, artist_id FROM album WHERE id = $albumId";
    $results = $db->executeQuery($sql);

    if (count($results) == 0) {
        header("Location: display_error.php?message=Album introuvable");
        exit;
    }
    $album = $results[0];
} catch (PDOException $ex) {
    header("Location: display_error.php?message=Erreur requête album");
    exit;
}
// recup artist dans la db
try {
    $artistId = $album['artist_id'];
    $sqlArtist = "SELECT name FROM artist WHERE id = $artistId";
    $artistResult = $db->executeQuery($sqlArtist);
    $artistName = (count($artistResult) > 0) ? $artistResult[0]['name'] : "Artiste Inconnu";
} catch (PDOException $ex) {
    $artistName = "Erreur récupération artiste";
}
// recup sons dans le db
$songsHtml = "";
try {

    $sqlSongs = "SELECT id, name, note, duration 
                 FROM song 
                 WHERE album_id = $albumId 
                 ORDER BY id ASC";

    $songsResults = $db->executeQuery($sqlSongs);

    if (count($songsResults) > 0) {
        $songsHtml .= '<div class="list-group list-group-flush">';
        $trackNumber = 1;

        foreach($songsResults as $song) {
            $sName = htmlspecialchars($song['name']);
            $sNote = htmlspecialchars($song['note']);
            $sDuration = formatDuration((int)$song['duration']);


            $badgeColor = ($sNote >= 15) ? 'text-bg-success' : 'text-bg-primary';

            $songsHtml .= <<<HTML
            <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="text-secondary me-3">#$trackNumber</span>
                    <i class="bi bi-music-note-beamed me-3 text-secondary"></i>
                    <div>
                        <strong>$sName</strong>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-muted">$sDuration</span>
                    <span class="badge $badgeColor rounded-pill">$sNote / 5</span>
                </div>
            </div>
HTML;
            $trackNumber++;
        }
        $songsHtml .= '</div>';
    } else {
        $songsHtml = '<p class="text-muted">Aucune chanson trouvée dans cet album.</p>';
    }

} catch (PDOException $ex) {
    header("Location: display_error.php?message=Erreur requête chansons");
    exit;
}
//HTML final
$albumName = htmlspecialchars($album['name']);
$albumCover = htmlspecialchars($album['cover']);
$releaseYear = substr($album['release_date'], 0, 4);
$songsCount = count($songsResults);

$html = <<<HTML
<div class="container bg-dark text-white p-4">

    <div class="mb-4">
        <a href="artists.php" class="btn btn-secondary">
            ← Retour à l'artiste
        </a>
    </div>

    <div class="row">

        <div class="col-md-4 text-center">
            <div class="card bg-dark border-0">
                <img src="$albumCover" alt="$albumName" class="img-fluid rounded shadow-lg mb-3" style="max-width: 300px;">
            </div>
        </div>

        <div class="col-md-8">
            <h5 class="text-uppercase text-secondary">Album</h5>
            <h1 class="display-4 fw-bold">$albumName</h1>
            
            <p class="lead">
                <span class="fw-bold">$artistName</span> • $releaseYear • $songsCount titres
            </p>
            
            <hr class="border-secondary my-4">
            
            <div class="mt-4">
                <h3 class="mb-3">Liste des titres</h3>
                $songsHtml
            </div>
        </div>
    </div>
</div>
HTML;

$page = new HTMLPage("Album : $albumName - Lowify");
$page->setupBootstrap([
    "class" => "bg-dark text-white",
    "data-bs-theme" => "dark"
]);
$page->addContent($html);

echo $page->render();