<?php
// 1. IMPORTATIONS
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatNumber($aMonthly_listeners) {
    if ($aMonthly_listeners >= 1000000000) {
        return round($aMonthly_listeners / 1000000000, 1) . 'B';
    }
    if ($aMonthly_listeners >= 1000000) {
        return round($aMonthly_listeners / 1000000, 1) . 'M';
    }
    if ($aMonthly_listeners >= 1000) {
        return round($aMonthly_listeners / 1000, 1) . 'k';
    }
    return (string)$aMonthly_listeners;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$artistId = (int) $_GET['id'];

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
    echo "Erreur requête artiste : " . $ex->getMessage();
    exit;
}


$albumHtml = "";
try {
    $sqlalbum = "SELECT id, name, cover
                 FROM album
                 WHERE artist_id = $artistId
                 ORDER BY release_date DESC";

    $albumResults = $db->executeQuery($sqlalbum);

    if (count($albumResults) > 0) {
        $albumHtml .= '<div class="list-group list-group-flush">';
        $rank = 1;
        foreach($albumResults as $album) {
            $aName = htmlspecialchars($album['name']);
            $sCover = htmlspecialchars($album['cover']);

            $albumHtml .= <<<HTML
            <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="text-secondary me-3">#$rank</span>
                    <img alt="$aName" src="$sCover" style="width: 50px; height: 50px; object-fit: cover; margin-right: 1rem; border-radius: 5px;">
                    <strong>$aName</strong>
                </div>
            </div>
HTML;
            $rank++;
        }
        $albumHtml .= '</div>';
    } else {
        $albumHtml = '<p class="text-muted">Aucun album trouvé.</p>';
    }

} catch (PDOException $ex) {
    echo "Erreur requête albums : " . $ex->getMessage();
    exit;
}


$songsHtml = "";
try {
    $sqlSongs = "SELECT s.id, s.name, s.note, a.cover
                 FROM song s
                 JOIN album a ON s.album_id = a.id
                 WHERE s.artist_id = $artistId
                 ORDER BY s.note DESC
                 LIMIT 5";

    $songsResults = $db->executeQuery($sqlSongs);

    if (count($songsResults) > 0) {
        $songsHtml .= '<div class="list-group list-group-flush">';
        $rank = 1;
        foreach($songsResults as $song) {
            $sName = htmlspecialchars($song['name']);
            $sNote = htmlspecialchars($song['note']);


            $sCover = htmlspecialchars($song['cover']);

            $badgeColor = ($sNote >= 15) ? 'text-bg-success' : 'text-bg-primary';

            $songsHtml .= <<<HTML
            <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="text-secondary me-3">#$rank</span>
                    <img alt="" src="$sCover" style="width: 50px; height: 50px; object-fit: cover; margin-right: 1rem; border-radius: 50%;">
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
$bioHtml = "";
try {
    $sqlbio = "SELECT biography,monthly_listeners
                 FROM artist
                 WHERE id = $artistId";


    $bioResults = $db->executeQuery($sqlbio);


    if (count($bioResults) > 0) {
        $bioHtml .= '<div class="list-group list-group-flush">';
        $rank = 1;
        foreach($bioResults as $bio) {
            $aBio = htmlspecialchars($bio['biography']);
            $aMonthly_listeners = htmlspecialchars($bio['monthly_listeners']);
            $aMonthly_listeners = formatNumber($bio['monthly_listeners']);

            $bioHtml .= <<<HTML
            <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a ><strong >$aMonthly_listeners played</strong></a>
                </div>
                <div class="d-flex align-items-center">
                    <strong>$aBio</strong>
                </div>
                
            </div>
HTML;
            $rank++;
        }
        $bioHtml .= '</div>';
    } else {
        $bioHtml = '<p class="text-muted">Aucun bio trouvé.</p>';
    }

} catch (PDOException $ex) {
    echo "Erreur requête albums : " . $ex->getMessage();
    exit;
}
// 6. AFFICHAGE
$name = htmlspecialchars($artist['name']);
$cover = htmlspecialchars($artist['cover']);

$html = <<<HTML
<div class="container bg-dark text-white p-4">
    <div class="mb-4">
        <a href="artists.php" class="btn btn-secondary">← Retour à la liste</a>
    </div>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="$cover" alt="$name" class="img-fluid rounded-circle shadow-lg mb-3" style="max-width: 300px;">
        </div>

        <div class="col-md-8">
            <h1 class="display-4">$name</h1>
            <p class="lead">Détails de l'artiste</p>
            <p>$bioHtml</p>
            <hr class="border-secondary">
            
            <div class="mt-4">
                <h3 class="mb-3">Top 5 des titres</h3>
                $songsHtml
            </div>
            
            <hr class="border-secondary mt-5">
            
            <div class="mt-4">
                <h3 class="mb-4">Liste des albums</h3>
                <div class="alert alert-dark border-0 p-0">
                    $albumHtml
                </div>
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