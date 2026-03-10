# Homelab Dashboard

A lightweight, static HTML dashboard for monitoring and accessing homelab services.

## Structure

```
.
├── index.html    # Main dashboard interface
├── data.json     # Service metrics (auto-updated)
├── icon.svg      # Dashboard logo
└── .gitignore   # Configured to ignore data.json
```

## Adding Services

1. Add a new service card in `index.html`:
   ```html
   <a href="https://service.example.com" class="card" data-service="service_id">
       <div class="icon-wrapper">
           <!-- SVG Icon Here -->
       </div>
       <div class="card-content">
           <span class="card-title">Service Name</span>
           <span class="card-subtitle">Service Description</span>
           <div class="status"></div>
       </div>
   </a>
   ```

2. Define display labels in the `serviceLabels` object within `index.html` (approx. line 450):
   ```javascript
   service_id: {
       metric_key_1: 'Display Label 1',
       metric_key_2: 'Display Label 2'
   }
   ```

## Updating Metrics

The dashboard dynamically fetches data from `data.json`. Use a script or cron job to update this file periodically:

```json
{
  "last_updated": "2026-03-09T12:55:00Z",
  "services": {
    "service_id": {
      "metric_key_1": "Value 1",
      "metric_key_2": "Value 2"
    }
  }
}
```

For array values (e.g., system load), the dashboard will display them as comma-separated values:
```json
"load_avg": [0.12, 0.15, 0.18]
```

## Example Update Script

Ensure you have `jq` installed to manipulate JSON from the command line.

```bash
#!/bin/bash
# update_dashboard.sh

# Example: Get system load average
LOAD=$(uptime | awk -F'load average: ' '{print $2}' | awk '{print $1","$2","$3}')

# Update data.json
jq --arg load "$LOAD" \
   '.last_updated = now | .services.system_node.load_avg = ($load | split(",") | map(select(. != "") | tonumber))' \
   data.json > tmp.json && mv tmp.json data.json
```

Add to crontab to run every minute:
```
* * * * * /path/to/update_dashboard.sh
```

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name dashboard.example.com;
    root /var/www/dashboard;
    index index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    # Ensure data.json can be fetched by the client
    location = /data.json {
        add_header Cache-Control "no-cache";
    }
}
```
