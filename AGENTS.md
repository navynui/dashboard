# Agents

This dashboard supports autonomous agents that perform background tasks and expose status/output interfaces.

## Hermes Agent

- **Purpose**: Runs scheduled chores and writes progress/report to `text.md`.
- **Display**: `hermes.html` renders all `.md`, `.txt`, `.csv` files as a blog feed sorted newest-first.
- **Server**: `http://192.168.31.243:7000/` — serves the dashboard (PHP + static files).
- **Docs**: See `HERMES.md`.
- **Blog Feed**: `hermes.html` uses `scan_files.php` to list files with previews, read/unread tracking (stored in `sys_get_temp_dir()`), inline delete, and hash‑based deep linking (`#file=xxx.md`).

## Future Agents

* Placeholder for future extensions
