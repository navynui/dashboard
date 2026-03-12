<?php
header('Content-Type: application/json');

$services = [
    'homeassistant' => 'http://192.168.31.182:8123/api/',
    'jellyfin' => 'http://192.168.31.243:8096/health',
    'qbittorrent' => 'http://192.168.31.243:8080/api/v2/app/preferences',
    'prowlarr' => 'http://192.168.31.243:9696/api/v1/system/status',
    'fintracker' => 'http://192.168.31.243:8000',
    'phpmyadmin' => 'http://192.168.31.243:8002',
    'fintrackerv2' => 'http://192.168.31.243:3000',
    'pgadmin' => 'http://192.168.31.234:8084',
    'proxmox' => 'http://192.168.31.241:8006',
    'camera' => 'http://192.168.31.244:8080',
    'blockyui' => 'http://192.168.31.243:8081',
    'sea' => 'http://192.168.31.244:80',
];

$status = [];

foreach ($services as $name => $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 1,
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status[$name] = $httpCode >= 200 && $httpCode < 400;
}

echo json_encode($status);