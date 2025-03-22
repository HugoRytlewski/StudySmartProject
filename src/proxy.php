<?php
if (!isset($_GET['url']) || empty($_GET['url'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Error: URL parameter is missing or empty';
    exit;
}

$url = $_GET['url'];
$response = file_get_contents($url);
header('Content-Type: text/calendar');
echo $response;