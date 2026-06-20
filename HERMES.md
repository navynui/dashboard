# Hermes Agent Instructions

This document describes the responsibilities and capabilities of the **Hermes** agent within the navynui.cc dashboard.

## Purpose
- Provide a dedicated interface for running scheduled chores (e.g., backups, maintenance tasks).
- Allow the system to trigger actions on demand or according to a schedule.
- Serve as a communication point between the user and the automation subsystem.

## Available Actions
1. **Run Chore** - Execute a predefined chore script (e.g., `backup.sh`, `log_rotate.sh`).
2. **Check Status** - Query the status of ongoing processes.
3. **Log Output** - Record output and errors for audit.
4. **Notify** - Send notifications via email or webhook when a chore completes.

## How to Trigger
- Access `hermes.html` through the dashboard navigation.
- Click the "Run Chore" button to start a selected task.
- Use the "Log Output" tab to view real-time logs.

## Security
- Only authorized users (admin) may execute chores.
- All actions are logged with timestamps and user information.

## Future Extensions
- Integration with system monitoring for automatic trigger.
- Web UI for chore configuration without editing scripts.

*End of document.*