<?php
header('Content-Type: application/json');

$dir = __DIR__;

// ── Pick a writable location for the status file ──
$statusFile = $dir . DIRECTORY_SEPARATOR . '.hermes_status.json';
$statusDir  = $dir;
if (!is_writable($dir) || (file_exists($statusFile) && !is_writable($statusFile))) {
    $fallback      = sys_get_temp_dir() . DIRECTORY_SEPARATOR . '.hermes_status_' . md5($dir) . '.json';
    $statusFile    = $fallback;
    $statusDir     = dirname($statusFile);
}

// ── Status helpers ──
function loadStatus($path) {
    if (!file_exists($path)) return [];
    $raw = @file_get_contents($path);
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveStatus($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// ── File helpers ──
function sanitizeFile($name) {
    $name = basename($name);
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['md', 'txt', 'csv'])) return null;
    return $name;
}

function getPreview($fullPath, $maxLen = 250) {
    $h = @fopen($fullPath, 'r');
    if (!$h) return '';
    $text = '';
    $read = 0;
    while (($line = fgets($h)) !== false && $read < $maxLen) {
        $text .= $line;
        $read = strlen($text);
    }
    fclose($h);
    if (strlen($text) > $maxLen) {
        $text = substr($text, 0, $maxLen);
        $lastSpace = strrpos($text, ' ');
        if ($lastSpace > $maxLen * 0.7) $text = substr($text, 0, $lastSpace);
        $text .= '…';
    }
    return $text;
}

function getTitle($name, $fullPath) {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext === 'md') {
        $h = @fopen($fullPath, 'r');
        if ($h) {
            while (($line = fgets($h)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '') continue;
                if (preg_match('/^#\s+(.+)/', $trimmed, $m)) {
                    fclose($h);
                    return trim($m[1]);
                }
                // First non-blank, non-heading line — use as title hint
                if (!preg_match('/^#{1,6}\s/', $trimmed)) {
                    $hint = mb_strlen($trimmed) > 80 ? mb_substr($trimmed, 0, 80) . '…' : $trimmed;
                    fclose($h);
                    return $hint;
                }
            }
            fclose($h);
        }
    }
    return preg_replace('/\.(md|txt|csv)$/i', '', $name);
}

// ── Handle actions ──
$action = $_GET['action'] ?? null;
$file   = isset($_GET['file']) ? sanitizeFile($_GET['file']) : null;

if ($action && !$file) {
    echo json_encode(['error' => 'Missing or invalid file parameter']);
    exit;
}

$status = loadStatus($statusFile);

if ($action === 'mark_read' && $file) {
    $status[$file] = ['read' => true, 'readAt' => date('c')];
    saveStatus($statusFile, $status);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'mark_unread' && $file) {
    $status[$file] = ['read' => false, 'readAt' => null];
    saveStatus($statusFile, $status);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'toggle_read' && $file) {
    $current = $status[$file]['read'] ?? false;
    $status[$file] = [
        'read'   => !$current,
        'readAt' => !$current ? date('c') : null,
    ];
    saveStatus($statusFile, $status);
    echo json_encode(['ok' => true, 'read' => !$current]);
    exit;
}

if ($action === 'delete' && $file) {
    $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        echo json_encode(['error' => 'File not found']);
        exit;
    }
    if (!is_writable($dir)) {
        echo json_encode(['error' => 'Directory not writable']);
        exit;
    }
    if (!is_writable($fullPath)) {
        // Attempt unlink regardless — on many setups unlink is governed by directory perms, not file perms
    }
    if (@unlink($fullPath)) {
        unset($status[$file]);
        saveStatus($statusFile, $status);
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete file']);
    }
    exit;
}

// ── Scan mode (default) ──
$items = scandir($dir);
$files = [];

if ($items) {
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
        if (!is_file($fullPath)) continue;
        $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
        if (!in_array($ext, ['md', 'txt', 'csv'])) continue;

        $mtime = filemtime($fullPath);
        $size  = filesize($fullPath);
        $lines = 0;
        $lc = @fopen($fullPath, 'r');
        if ($lc) {
            while (!feof($lc)) {
                if (fgets($lc) !== false) $lines++;
            }
            fclose($lc);
        }

        $files[] = [
            'name'    => $item,
            'title'   => getTitle($item, $fullPath),
            'mtime'   => $mtime,
            'date'    => date('Y-m-d', $mtime),
            'size'    => $size,
            'type'    => $ext,
            'preview' => getPreview($fullPath),
            'read'    => $status[$item]['read'] ?? false,
            'lines'   => $lines,
        ];
    }
}

// Sort by mtime descending (most recent first)
usort($files, function ($a, $b) {
    return $b['mtime'] - $a['mtime'];
});

// Garbage-collect status entries for files no longer on disk
$activeNames = array_column($files, 'name');
$changed = false;
foreach ($status as $name => $val) {
    if (!in_array($name, $activeNames)) {
        unset($status[$name]);
        $changed = true;
    }
}
if ($changed) saveStatus($statusFile, $status);

echo json_encode(array_values($files));
