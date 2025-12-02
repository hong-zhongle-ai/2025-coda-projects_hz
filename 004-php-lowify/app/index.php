<?php
// 1. IMPORTATIONS
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

// recup les artist trier par le plus d'ecoutes du plus grand u plus petit et album du plus recent au plus vieux dans la db
$topArtists = [];
try {
    $topArtists = $db->executeQuery("
        SELECT id, name, cover 
        FROM artist 
        ORDER BY monthly_listeners DESC 
        LIMIT 5
    ");
} catch (PDOException $ex) {
    echo "Erreur Top Artistes : " . $ex->getMessage();
}

$recentAlbums = [];
try {
    $recentAlbums = $db->executeQuery("
        SELECT album.id, album.name, album.cover, album.release_date, artist.name as artist_name
        FROM album
        JOIN artist ON album.artist_id = artist.id
        ORDER BY album.release_date DESC
        LIMIT 5
    ");
} catch (PDOException $ex) {
    echo "Erreur Top Sorties : " . $ex->getMessage();
}
// recup les meilleure sons trier par la note du plus grand au plus petit avec une limite de 5
$topRatedAlbums = [];
try {
    $topRatedAlbums = $db->executeQuery("
        SELECT 
            album.id, 
            album.name, 
            album.cover, 
            artist.name as artist_name,
            (SELECT AVG(note) FROM song WHERE album_id = album.id) as avg_note
        FROM album
        JOIN artist ON album.artist_id = artist.id
        ORDER BY avg_note DESC
        LIMIT 5
    ");
} catch (PDOException $ex) {
    echo "Erreur Top Rated : " . $ex->getMessage();
}
//top des artists
$htmlTrending = "";
if (count($topArtists) > 0) {
    $htmlTrending .= '<div class="row flex-nowrap overflow-auto pb-3 mb-4 section-scroll">';
    foreach ($topArtists as $artist) {
        $name = htmlspecialchars($artist['name']);
        $cover = htmlspecialchars($artist['cover']);
        $id = $artist['id'];

        $htmlTrending .= <<<HTML
        <div class="col-lg-3 col-md-4 col-6">
            <a href="artist.php?id=$id" class="text-decoration-none text-white">
                <div class="card h-100 bg-dark text-white border-secondary shadow-sm hover-scale p-3">
                    <div class="text-center mb-3">
                        <img src="$cover" class="rounded-circle shadow" alt="$name" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <div class="text-center">
                        <h6 class="fw-bold text-truncate">$name</h6>
                        <span class="badge bg-secondary">Artiste</span>
                    </div>
                </div>
            </a>
        </div>
HTML;
    }
    $htmlTrending .= '</div>';
}
// les album les plus recent
$htmlReleases = "";
if (count($recentAlbums) > 0) {
    $htmlReleases .= '<div class="row flex-nowrap overflow-auto pb-3 mb-4 section-scroll">';
    foreach ($recentAlbums as $album) {
        $name = htmlspecialchars($album['name']);
        $cover = htmlspecialchars($album['cover']);
        $artist = htmlspecialchars($album['artist_name']);
        $id = $album['id'];
        $year = substr($album['release_date'], 0, 4);

        $htmlReleases .= <<<HTML
        <div class="col-lg-2 col-md-3 col-5">
            <a href="album.php?id=$id" class="text-decoration-none text-white">
                <div class="card h-100 bg-dark text-white border-0 hover-bg">
                    <img src="$cover" class="card-img-top rounded shadow mb-2" alt="$name" style="aspect-ratio: 1/1; object-fit: cover;">
                    <h6 class="text-truncate mb-0 fw-bold" title="$name">$name</h6>
                    <p class="text-secondary small mb-0 text-truncate">$artist</p>
                    <small class="text-muted">$year</small>
                </div>
            </a>
        </div>
HTML;
    }
    $htmlReleases .= '</div>';
}
// les mieux noter
$htmlTopRated = "";
if (count($topRatedAlbums) > 0) {
    $htmlTopRated .= '<div class="row flex-nowrap overflow-auto pb-3 mb-4 section-scroll">';
    foreach ($topRatedAlbums as $album) {
        $name = htmlspecialchars($album['name']);
        $cover = htmlspecialchars($album['cover']);
        $artist = htmlspecialchars($album['artist_name']);
        $id = $album['id'];
        $note = number_format($album['avg_note'], 1);
        $noteColor = ($album['avg_note'] >= 15) ? 'text-bg-success' : 'text-bg-primary';

        $htmlTopRated .= <<<HTML
        <div class="col-lg-2 col-md-3 col-5">
            <a href="album.php?id=$id" class="text-decoration-none text-white">
                <div class="card h-100 bg-dark text-white border-0 hover-bg position-relative">
                    <span class="position-absolute top-0 end-0 badge $noteColor m-2 shadow">$note / 5</span>
                    <img src="$cover" class="card-img-top rounded shadow mb-2" alt="$name" style="aspect-ratio: 1/1; object-fit: cover;">
                    <h6 class="text-truncate mb-0 fw-bold" title="$name">$name</h6>
                    <p class="text-secondary small mb-0 text-truncate">$artist</p>
                </div>
            </a>
        </div>
HTML;
    }
    $htmlTopRated .= '</div>';
}

// HTML final
$html = <<<HTML
<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.05); border-color: white !important; }
    
    .hover-bg { transition: background-color 0.2s; padding: 10px; border-radius: 8px; }
    .hover-bg:hover { background-color: #282828 !important; }

    .section-scroll::-webkit-scrollbar { height: 8px; }
    .section-scroll::-webkit-scrollbar-track { background: #121212; }
    .section-scroll::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
    .section-scroll::-webkit-scrollbar-thumb:hover { background: #555; }
</style>
<div class="p-5 mb-4 bg-dark rounded-3" style="background: linear-gradient(45deg, #1db954, #191414);">
    <div class="container-fluid py-3 text-white">
        <h1 class="display-5 fw-bold">Bienvenue sur Lowify</h1>
        <p class="col-md-8 fs-4">Découvrez les artistes du moment, les dernières sorties et les albums les mieux notés par la communauté.</p>
        
        <div class="mt-4">
            <!-- Modification du formulaire ici -->
            <form action="search.php" method="GET" class="d-flex" role="search">
                <input class="form-control form-control-lg me-2 rounded-pill border-0" type="text" name="query" placeholder="Rechercher un artiste, un album ou un titre..." aria-label="Search" style="max-width: 500px;" required>
                <button class="btn btn-light btn-lg rounded-pill px-4 fw-bold" type="submit">Rechercher</button>
            </form>
        </div>

        <a href="artists.php" class="btn btn-outline-light rounded-pill mt-4 btn-sm">Voir tout le catalogue</a>
    </div>
</div>

<div class="container-fluid bg-dark text-white p-4">
    <div class="mb-5">
        <h2 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Top trending</h2>
        $htmlTrending
    </div>

    <div class="mb-5">
        <h2 class="fw-bold mb-3"><i class="bi bi-disc me-2 text-info"></i>Top sorties</h2>
        $htmlReleases
    </div>

    <div class="mb-5">
        <h2 class="fw-bold mb-3"><i class="bi bi-star-fill me-2 text-warning"></i>Top albums</h2>
        $htmlTopRated
    </div>
</div>
HTML;

// 6. RENDU
$page = new HTMLPage("Accueil - Lowify");
$page->setupBootstrap([
    "class" => "bg-dark text-white",
    "data-bs-theme" => "dark"
]);
$page->setupNavigationTransition();
$page->addContent($html);

echo $page->render();