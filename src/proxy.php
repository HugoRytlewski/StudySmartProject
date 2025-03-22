<?php
$url = $_GET['url'];
$response = file_get_contents($url);
header('Content-Type: text/calendar');
echo $response;