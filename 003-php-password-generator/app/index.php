<?php

$password = "";
$length = 8;
$useLower = true;
$useUpper = true;
$useNum   = true;
$useSym   = true;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $length = (int)$_POST['size'] ?? 0;

    $useLower = $_POST['use-alpha-min'] ?? 0;
    $useUpper = $_POST['use-alpha-maj'] ?? 0;
    $useNum   = $_POST['use-num'] ?? 0;
    $useSym   = $_POST['use-symbols'] ?? 0;

    $chars = "";
    $lowerSet = "abcdefghijklmnopqrstuvwxyz";
    $upperSet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $numSet   = "0123456789";
    $symSet   = "!@#$%^&*()-_=+";

    $chars = "";
    $tempPassword = "";

    if ($useLower) {
        $chars .= $lowerSet;
        $tempPassword .= $lowerSet[random_int(0, strlen($lowerSet) - 1)];
    }
    if ($useUpper) {
        $chars .= $upperSet;
        $tempPassword .= $upperSet[random_int(0, strlen($upperSet) - 1)];
    }
    if ($useNum) {
        $chars .= $numSet;
        $tempPassword .= $numSet[random_int(0, strlen($numSet) - 1)];
    }
    if ($useSym) {
        $chars .= $symSet;
        $tempPassword .= $symSet[random_int(0, strlen($symSet) - 1)];
    }

    if ($chars === "") {
        $password = "Error: Please select at least one option!";
    } else {
        $remainingLength = $length - strlen($tempPassword);

        if ($remainingLength > 0) {
            $maxIndex = strlen($chars) - 1;
            for ($i = 0; $i < $remainingLength; $i++) {
                $tempPassword .= $chars[random_int(0, $maxIndex)];
            }
        }

        $passArray = str_split($tempPassword);

        for ($i = count($passArray) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            // Swap elements
            $temp = $passArray[$i];
            $passArray[$i] = $passArray[$j];
            $passArray[$j] = $temp;
        }

        $password = implode('', $passArray);

        $password = substr($password, 0, $length);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Password Generator</title>
    <style>
        body { padding: 40px; max-width: 600px; margin: auto; background-color: gray}
        .result-box { background: lightgrey; padding: 15px; font-family: monospace; font-size: 1.5rem; text-align: center; margin-bottom: 20px; border-radius: 5px;}
    </style>
</head>
<body>
<?php if ($password): ?>
    <div class="alert alert-success text-center">
        <strong>Votre mot de passe :</strong><br>
        <div class="result-box"><?= htmlspecialchars($password) ?></div>
    </div>
<?php endif; ?>
<form method="POST" action="index.php">
    <div class="mb-3">
        <label for="size" class="form-label">Taille</label>
        <select class="form-select" name="size" id="size">
            <?php
            // Loop to generate options from 8 to 42
            for ($i = 8; $i <= 42; $i++) {
                // Check if this was the selected size previously
                $selected = ($i == $length) ? 'selected' : '';
                echo "<option value='$i' $selected>$i</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="1" id="use-alpha-min" name="use-alpha-min"
            <?= $useLower ? 'checked' : '' ?>>
        <label class="form-check-label" for="use-alpha-min">
            Utiliser les lettres minuscules (a-z)
        </label>
    </div>
    <div class="form-check">
        <input class="" type="checkbox" value="1" id="use-alpha-maj" name="use-alpha-maj"
            <?= $useUpper ? 'checked' : '' ?>>
        <label class="form-check-label" for="use-alpha-maj">
            Utiliser les lettres majuscules (A-Z)
        </label>
    </div>
    <div class="form-check">
        <input class="" type="checkbox" value="1" id="use-num" name="use-num"
            <?= $useNum ? 'checked' : '' ?>>
        <label class="form-check-label" for="use-num">
            Utiliser les chiffres (0-9)
        </label>
    </div>
    <div class="form-check mb-3">
        <input class="" type="checkbox" value="1" id="use-symbols" name="use-symbols"
            <?= $useSym ? 'checked' : '' ?>>
        <label class="form-check-label" for="use-symbols">
            Utiliser les symboles (!@#$%^&*())
        </label>
    </div>
    <div class="">
        <button type="submit" class="btn btn-primary w-100">Générer !</button>
    </div>
</form>
</body>
</html>
