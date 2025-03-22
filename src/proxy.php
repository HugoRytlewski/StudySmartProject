<?php
if (isset($_GET['url']) && filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    $url = $_GET['url'];
    $response = @file_get_contents($url);

    if ($response === false) {
        // Gérer l'erreur si la récupération échoue
        echo "Erreur lors de la récupération des données.";
    } else {
        header('Content-Type: text/calendar');
        echo $response;
    }
} else {
    echo "URL invalide.";
}
?>
