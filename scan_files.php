<?php
header('Content-Type: application/json');

$dir = __DIR__;
$files = [];

try {
    $items = scandir($dir);
    if ($items) {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_file($fullPath) && in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), ['md', 'txt', 'csv'])) {
                $files[] = $item;
            }
        }
    }
} catch (Throwable $e) {
    echo json_encode([]);
    exit;
}

// Sort: text.md first, then alphabetical
usort($files, function ($a, $b) {
    if ($a === 'text.md') return -1;
    if ($b === 'text.md') return 1;
    return strcasecmp($a, $b);
});

echo json_encode(array_values($files));
