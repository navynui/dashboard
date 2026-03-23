# Cloud Hub Dashboard

A lightweight, PHP-powered dashboard for monitoring and accessing personal web services.

## Structure

```
.
├── index.html    # Main dashboard interface (Frontend)
├── status.php    # Service status checker (Backend)
├── icon.svg      # Dashboard logo
└── .gitignore    # Standard git ignore rules
```

## How It Works

1.  **Backend (`status.php`)**: Periodically pinged by the frontend. It uses cURL to check if the configured services are reachable (HTTP 2xx, 3xx, or 401).
2.  **Frontend (`index.html`)**: Fetches status data from `status.php` and updates the UI with "online" status dots.

## Adding Services

To add a new service, you need to update two files:

### 1. Update `status.php`
Add the service identifier and its internal URL to the `$services` array:
```php
$services = [
    // ... existing services
    'my_new_service' => 'http://192.168.31.XXX:PORT/',
];
```

### 2. Update `index.html`
Add a new service card in the `<main class="grid">` section. Ensure the `data-service` attribute matches the key used in `status.php`:
```html
<a href="https://service.example.com" class="card" data-service="my_new_service">
    <div class="icon-wrapper">
        <!-- SVG Icon Here (e.g., from Lucide or Heroicons) -->
    </div>
    <div class="card-content">
        <span class="card-title">Service Name</span>
        <span class="card-subtitle">Service Description</span>
    </div>
    <span class="status-dot"></span>
</a>
```

## Features

- **Modern Design**: Built with 'Outfit' font and a sleek, dark-themed responsive grid.
- **Real-time Status**: Periodic status checks via PHP/cURL.
- **Glassmorphism**: Subtle backdrop filters and gradients for a premium look.
- **Secure**: Disables SSL verification for internal services (e.g., Proxmox) to ensure connectivity.

## Nginx Configuration

Recommended configuration for serving with PHP support:

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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock; # Adjust PHP version as needed
    }

    location = /status.php {
        add_header Cache-Control "no-cache";
    }
}
```

## Development

The dashboard is designed to be simple and easy to extend. All icons are inline SVGs for better performance and customization.
