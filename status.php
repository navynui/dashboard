<?php
header('Content-Type: application/json');

$services = [
    'homeassistant' => 'http://192.168.31.182:8123/',
    'jellyfin' => 'https://jellyfin.navynui.cc/',
    'qbittorrent' => 'http://192.168.31.243:8080/',
    'prowlarr' => 'http://192.168.31.243:9696/',
    'fintracker' => 'https://fin.navynui.cc/',
    'dbgate' => 'http://192.168.31.243:8002/',
    'fintrackerv2' => 'http://192.168.31.243:3000/',
    'codeserver' => 'http://192.168.31.243:2000/',
    'proxmox' => 'https://192.168.31.241:8006/',
    'camera' => 'http://192.168.31.244:8080/',
    'blockyui' => 'http://192.168.31.243:8081/',
    'sea' => 'http://192.168.31.244:80/',
    'llama_cpp' => 'http://192.168.31.129:8080/',
    'llm_mobile' => 'http://192.168.31.129:8000/',
    'comfyui' => 'http://192.168.31.129:8188/',
];

function get_system_stats() {
    $stats = [
        'uptime' => 'Unknown',
        'load' => '0.00, 0.00, 0.00',
        'memory' => 0,
        'cpu' => 'Intel(R) Core(TM) i7-5500U CPU @ 2.40GHz'
    ];

    // Uptime: Try shell_exec first, then fallback to /proc/uptime
    $uptime_info = @shell_exec('uptime -p');
    if ($uptime_info) {
        $stats['uptime'] = trim(str_replace('up ', '', $uptime_info));
    } else {
        $uptime_data = @file_get_contents('/proc/uptime');
        if ($uptime_data) {
            $seconds = (int)explode(' ', $uptime_data)[0];
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $mins = floor(($seconds % 3600) / 60);
            if ($days > 0) $stats['uptime'] = "$days days, $hours hours, $mins mins";
            else if ($hours > 0) $stats['uptime'] = "$hours hours, $mins mins";
            else $stats['uptime'] = "$mins mins";
        }
    }
    
    // Load: Use built-in function
    $load_info = sys_getloadavg();
    if ($load_info) $stats['load'] = implode(', ', array_map(fn($l) => number_format($l, 2), $load_info));

    // Memory: Try /proc/meminfo for container compatibility
    $meminfo = @file_get_contents('/proc/meminfo');
    if ($meminfo) {
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
        if ($total && $available) {
            $used = (int)$total[1] - (int)$available[1];
            $stats['memory'] = round(($used / (int)$total[1]) * 100);
        }
    } else {
        // Fallback to free command
        $free = @shell_exec('free');
        if ($free) {
            $free_arr = explode("\n", (string)trim($free));
            if (isset($free_arr[1])) {
                $mem = explode(" ", preg_replace("/\s+/", " ", $free_arr[1]));
                if (isset($mem[1]) && isset($mem[2]) && $mem[1] > 0) {
                    $stats['memory'] = round(($mem[2] / $mem[1]) * 100);
                }
            }
        }
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
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 1,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; StatusBot/1.0)',
    ];

    if (strpos($url, 'https://') === 0) {
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
    }

    curl_setopt_array($ch, $curlOptions);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_errno($ch);
    curl_close($ch);

    $status['services'][$name] = (($httpCode >= 200 && $httpCode < 400) || $httpCode === 401) && $curlErr === 0;
}

echo json_encode($status);