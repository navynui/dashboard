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

function get_system_stats() {
    $stats = [
        'uptime' => 'Unknown',
        'load' => '0.00, 0.00, 0.00',
        'memory' => 0,
        'cpu' => 'Unknown CPU'
    ];

    // Uptime and Load
    $uptime_info = shell_exec('uptime -p');
    if ($uptime_info) $stats['uptime'] = trim(str_replace('up ', '', $uptime_info));
    
    $load_info = sys_getloadavg();
    if ($load_info) $stats['load'] = implode(', ', array_map(fn($l) => number_format($l, 2), $load_info));

    // Memory
    $free = shell_exec('free');
    if ($free) {
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", preg_replace("/\s+/", " ", $free_arr[1]));
        $mem_total = $mem[1];
        $mem_used = $mem[2];
        $stats['memory'] = round(($mem_used / $mem_total) * 100);
    }

    // CPU Info
    $cpu_info = shell_exec('lscpu | grep "Model name"');
    if ($cpu_info) {
        $stats['cpu'] = trim(str_replace('Model name:', '', $cpu_info));
    }

    return $stats;
}

$status = [
    'services' => [],
    'system' => get_system_stats()
];

foreach ($services as $name => $url) {
    $ch = curl_init($url);
    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 2, // Slightly shorter timeout for snappier refresh
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 1,
    ];

    if (strpos($url, 'https://') === 0) {
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
    }

    curl_setopt_array($ch, $curlOptions);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status['services'][$name] = ($httpCode >= 200 && $httpCode < 400) || $httpCode === 401;
}

echo json_encode($status);