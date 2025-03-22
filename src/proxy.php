<?php
if (empty($_GET['url'])) {
    http_response_code(400); 
    echo 'Error: URL parameter is missing or empty.';
    exit;
}

$url = $_GET['url'];

// Validar que la URL sea válida
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo 'Error: Invalid URL.';
    exit;
}

$response = @file_get_contents($url);

if ($response === false) {
    http_response_code(555); 
    echo 'Error: Unable to fetch the URL.';
    exit;
}

header('Content-Type: text/calendar');
echo $response;