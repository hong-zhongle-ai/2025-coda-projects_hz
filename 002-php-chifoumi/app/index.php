<?php

$choices = ['rock', 'paper', 'scissor'];
$player = $_GET['player'] ?? null;
if ($player == null) {
    $message = "<p><strong>fait ton choix</strong></p>";
} else {
    $computer_index = array_rand($choices);
    $computer = $choices[$computer_index];
    if ($computer === $player) {
        $result = "<p style=color:yellow><strong>draw</strong></p>";
        $result_class = "draw";
    } elseif (
        ($player === 'rock' && $computer === 'scissor') ||
        ($player === 'paper' && $computer === 'rock') ||
        ($player === 'scissor' && $computer === 'paper')
    ) {
        $result = "<p style=color:green><strong>win</strong></p>";
        $result_class = "win";
    } else {
        $result = "<p style=color:Red><strong>lose</strong>";
        $result_class = "lose";
    }

    $message = "<p>Ton choix : <strong>$player</strong></p>";
    $message .= "<p>Choix de l'ordinateur : <strong>$computer</strong></p>";
    $message .= "<h3 class=\"$result_class\">$result</h3>";
}
$html = <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Chifumi</title>
</head>
<style>
    body { font-family: sans-serif; text-align: center; }
    h1 { color: red; }
    button { padding: 10px 20px; margin: 5px; }
    a { text-decoration: none; color: black; }
</style>
<body>
    <h1>Chifumi</h1>
    <h2>Fais ton choix</h2>
    
    <button type="button"><a href="?player=rock">rock</a></button>
    <button type="button"><a href="?player=paper">paper</a></button>
    <button type="button"><a href="?player=scissor">scissor</a></button>
    
    <hr>
    
    <h2>Statut de la partie</h2>
    
    $message
    
   <button type="button"><a href="./">retry</a></button>
    
    
    
</body>
</html>
HTML;

// Afficher le code HTML complet
echo $html;
