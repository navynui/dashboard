# Homelab Dashboard

Static HTML dashboard for homelab services.

## Structure

```
.
├── index.html    # Main dashboard
├── data.json     # Service metrics (auto-updated)
├── icon.svg      # Logo
└── .gitignore   # Ignores data.json
```

## Adding Services

1. Add a new card in `index.html`:
   ```html
   <a href="https://service.url" class="card" data-service="servicename">
       <div class="icon-wrapper">...</div>
       <div class="card-content">
           <span class="card-title">Service Name</span>
           <span class="card-subtitle">Description</span>
           <div class="status"></div>
       </div>
   </a>
   ```

2. Add labels in `serviceLabels` object (line ~442):
   ```javascript
   servicename: {
       param1: 'Label 1',
       param2: 'Label 2'
   }
   ```

## Updating Metrics

The server script should update `data.json` every minute via cron:

```json
{
  "last_updated": "2026-03-09T12:55:00Z",
  "services": {
    "servicename": {
      "param1": "value1",
      "param2": "value2"
    }
  }
}
```

For array values (e.g., load average), use:
```json
"load_avg": [0.12, 0.15, 0.18]
```

## Example Cron Script

```bash
#!/bin/bash
# update_dashboard.sh

# Get load average
LOAD=$(uptime | awk -F'load average: ' '{print $2}' | awk '{print $1","$2","$3}')

# Update data.json (jq required)
jq --arg load "$LOAD" \
   '.last_updated = now | .services.proxmox.load_avg = ($load | split(",") | map(select(. != "") | tonumber))' \
   data.json > tmp.json && mv tmp.json data.json
```

Add to crontab:
```
* * * * * /path/to/update_dashboard.sh
```

## Nginx Config

```nginx
server {
    listen 80;
    server_name dashboard.example.com;
    root /var/www/dashboard;
    index index.html;

    location / {
        try_files $uri $uri/ =404;
    }
}
```
