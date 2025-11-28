<?php
// 1. IMPORTATIONS
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// On sécurise l'ID en s'assurant que c'est bien un nombre entier
$artistId = (int) $_GET['id'];
$songId = (int) $_GET['id'];

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

try {
    $sql = "SELECT id, name, cover FROM artist WHERE id = $artistId";

    $results = $db->executeQuery($sql);

    if (count($results) == 0) {
        echo "Artiste introuvable.";
        exit;
    }

    $artist = $results[0];

} catch (PDOException $ex) {
    echo "Erreur requête : " . $ex->getMessage();
    exit;
}
$songsHtml = "";
try {
    $sqlSongs = "SELECT id, name, note
                 FROM song
                 WHERE artist_id = $artistId
                 ORDER BY note DESC
                 LIMIT 5";

    $songsResults = $db->executeQuery($sqlSongs);

    if (count($songsResults) > 0) {
        $songsHtml .= '<div class="list-group list-group-flush">';
        $rank = 1;
        foreach($songsResults as $song) {
            $sName = htmlspecialchars($song['name']);
            $sNote = htmlspecialchars($song['note']);

            $badgeColor = ($sNote >= 15) ? 'text-bg-success' : 'text-bg-primary';

            $songsHtml .= <<<HTML
            <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-secondary me-3">#$rank</span>
                    <strong>$sName</strong>
                </div>
                <span class="badge $badgeColor rounded-pill">$sNote / 5</span>
            </div>
HTML;
            $rank++;
        }
        $songsHtml .= '</div>';
    } else {
        $songsHtml = '<p class="text-muted">Aucune chanson trouvée pour cet artiste.</p>';
    }

} catch (PDOException $ex) {
    echo "Erreur requête songs : " . $ex->getMessage();
    exit;
}

$name = $artist['name'];
$cover = $artist['cover'];
$html = <<<HTML
<div class="container bg-dark text-white p-4">
    <div class="mb-4">
        <a href="https://localhost/artists.php" class="btn btn-secondary">← Retour à la liste</a>
    </div>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="$cover" alt="$name" class="img-fluid rounded-circle shadow-lg mb-3" style="max-width: 300px;">
        </div>

        <div class="col-md-8">
            <h1 class="display-4">$name</h1>
            <p class="lead">Détails de l'artiste</p>
            <hr class="border-secondary">
            
            
            <div class="mt-4">
                <h3 class="mb-3">Top 5 des titres</h3>
                $songsHtml
            </div>
            <hr class="border-secondary">
            
            <div class="alert alert-info bg-secondary-subtle border-0 text-white">
                Liste des albums
            </div>
        </div>
    </div>
</div>
HTML;

$page = new HTMLPage("Artiste : $name - Lowify");
$page->setupBootstrap([
    "class" => "bg-dark text-white",
    "data-bs-theme" => "dark"
]);
$page->addContent($html);

echo $page->render();