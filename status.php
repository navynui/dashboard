<?php
header('Content-Type: application/json');

$services = [
    'homeassistant' => 'http://192.168.31.182:8123/',
    'jellyfin' => 'http://192.168.31.243:8096/',
    'qbittorrent' => 'http://192.168.31.243:8080/',
    'prowlarr' => 'http://192.168.31.243:9696/',
    'fintracker' => 'http://192.168.31.243:8000/',
    'dbgate' => 'http://192.168.31.243:8002/',
    'fintrackerv2' => 'http://192.168.31.243:3000/',
    'codeserver' => 'http://192.168.31.243:2000/',
    'proxmox' => 'https://192.168.31.241:8006/',
    'camera' => 'http://192.168.31.244:8080/',
    'blockyui' => 'http://192.168.31.243:8081/',
    'sea' => 'http://192.168.31.244:80/',
];

$status = [];

foreach ($services as $name => $url) {
    $ch = curl_init($url);
    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 1,
    ];

    // Disable SSL verification for internal HTTPS (e.g. Proxmox)
    if (strpos($url, 'https://') === 0) {
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
    }

    curl_setopt_array($ch, $curlOptions);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status[$name] = $httpCode >= 200 && $httpCode < 400 || $httpCode === 401;
}

echo json_encode($status);