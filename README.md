# Cloud Hub Dashboard

A lightweight, PHP-powered dashboard for monitoring and accessing personal web services, with an integrated agent report viewer.

## Structure

```
.
├── index.html              # Main dashboard interface (Frontend)
├── status.php              # Service status + system stats checker (Backend)
├── hermes.html             # Markdown/blog feed viewer (Frontend)
├── scan_files.php          # Directory scanner API (Backend)
├── icon.svg                # Dashboard logo
├── AGENTS.md               # Agent documentation
├── HERMES.md               # Hermes agent instructions
├── .gitignore              # Standard git ignore rules
├── .hermes_status.json     # Read/unread tracking for the blog feed
│
├── text.md                 # Hermes agent progress/report output
├── axum.md                 # Documentation / report files
├── axum8.md
├── htmx.md
├── htmx4.md
├── vllm.md
├── UpdateLlamaCPP.md
├── models.json
├── localranking.csv
├── MixScore.csv
└── image_prompt.txt
```

## How It Works

1. **Backend (`status.php`)**: Periodically pinged by the frontend. It uses cURL to check if configured services are reachable (HTTP 2xx, 3xx, or 401), and also reports system stats (uptime, CPU load, memory usage).
2. **Frontend (`index.html`)**: Fetches status data from `status.php` and renders service cards with "online" status dots. Displays system stats in a header bar.
3. **Blog Feed (`hermes.html`)**: Renders all `.md`, `.txt`, `.csv` files as a blog feed sorted newest-first. Uses `scan_files.php` to list files with previews, read/unread tracking (stored in `.hermes_status.json`), inline delete, and hash‑based deep linking (`#file=xxx.md`).

## Services

| Service         | URL                           |
|-----------------|-------------------------------|
| Home Assistant  | http://192.168.31.182:8123/   |
| Jellyfin        | https://jellyfin.navynui.cc/  |
| qBittorrent     | http://192.168.31.243:8080/   |
| Prowlarr        | http://192.168.31.243:9696/   |
| FinTracker      | https://fin.navynui.cc/       |
| DBGate          | http://192.168.31.243:8002/   |
| FinTracker v2   | http://192.168.31.243:3000/   |
| Code Server     | http://192.168.31.243:2000/   |
| Proxmox         | https://192.168.31.241:8006/  |
| Camera          | http://192.168.31.244:8080/   |
| Blocky UI       | http://192.168.31.243:8081/   |
| SEA             | http://192.168.31.244:80/     |
| llama.cpp       | http://192.168.31.129:8080/   |
| LLM Mobile      | http://192.168.31.129:8000/   |
| ComfyUI         | http://192.168.31.129:8188/   |

## Adding a Service

Update two files:

### 1. `status.php`

Add to the `$services` array:

```php
$services = [
    // ... existing services
    'my_new_service' => 'http://192.168.31.XXX:PORT/',
];
```

### 2. `index.html`

Add a service card in `<main class="grid">`. The `data-service` attribute must match the key used in `status.php`:

```html
<a href="https://service.example.com" class="card" data-service="my_new_service">
    <div class="icon-wrapper">
        <!-- SVG Icon Here -->
    </div>
    <div class="card-content">
        <span class="card-title">Service Name</span>
        <span class="card-subtitle">Description</span>
    </div>
    <span class="status-dot"></span>
</a>
```

## Features

- **Modern Design**: Built with 'Outfit' font, dark-themed responsive grid.
- **Real-time Status**: Periodic service health checks via PHP/cURL.
- **System Stats**: Uptime, CPU load, and memory usage displayed in the header.
- **Glassmorphism**: Subtle backdrop filters and gradients for a premium look.
- **Secure**: Disables SSL verification for internal services (e.g., Proxmox) to ensure connectivity.
- **Blog Feed**: Automatically discovers `.md`, `.txt`, `.csv` files and renders them as a sorted feed with read/unread tracking, previews, and inline delete.

## Agents

See [AGENTS.md](AGENTS.md) for details on the Hermes agent and future extensions.

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name dashboard.example.com;
    root /home/nui/dev/dashboard;
    index index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    location = /status.php {
        add_header Cache-Control "no-cache";
    }

    location = /scan_files.php {
        add_header Cache-Control "no-cache";
    }
}
```

## Development

The dashboard is designed to be simple and easy to extend. All icons are inline SVGs for better performance and customization. Report/documentation files are discovered automatically — just drop a `.md`, `.txt`, or `.csv` file in the dashboard directory and it will appear in the blog feed.
