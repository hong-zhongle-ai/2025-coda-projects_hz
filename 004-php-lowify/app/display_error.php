<?php
require_once 'inc/page.inc.php';

$msgErreur = "Une erreur inconnue est survenue.";
//si empty n'ai pas vide donc ca veut dire que il n'a pas trouver
if (!empty($_GET['message'])) {
    $msgErreur = htmlspecialchars($_GET['message']);
}
//affichage HTML
$html = <<<HTML
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <!-- Carte d'alerte stylisée -->
            <div class="alert alert-danger shadow-lg border-0 rounded-3 p-4" role="alert">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-1 me-3 text-danger"></i>
                    <div>
                        <h4 class="alert-heading mb-0 fw-bold">Oups ! Une erreur est survenue.</h4>
                    </div>
                </div>
                
                <!-- Affichage du message d'erreur -->
                <p class="fs-5 text-secondary mb-0">$msgErreur</p>
                
                <hr class="my-4 border-danger-subtle">
                
                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-danger px-4 rounded-pill fw-bold">Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;

$page = new HTMLPage("Erreur - Lowify");

$page->setupBootstrap([
    "class" => "bg-light d-flex align-items-center justify-content-center vh-100"
]);

$page->addContent($html);
echo $page->render();