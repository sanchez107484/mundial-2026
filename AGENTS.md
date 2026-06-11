# AGENTS.md

## Stack

PHP 8.2 + Apache, vanilla JS, no build tools, no package manager, no tests, no linter.

## Local dev

XAMPP at `http://localhost/mundial2026/`. No setup commands needed — just place files in `htdocs/mundial2026/`.

## Architecture

- **Data layer**: JSON flat files in `data/` — no database. `readJSON()`/`writeJSON()` in `includes/functions.php` are the only data access functions.
- **`data/teams.json`** is the master config: groups, knockout bracket, scoring rules, entry fee, and prize distribution all live here.
- **`.htaccess`** blocks direct HTTP access to `data/`, `config/`, `includes/`, and all `.json` files.
- **API routing**: all endpoints use `?action=` query params (e.g. `api/auth.php?action=login`), not REST paths.
- **`BASE_URL`** auto-detects context: `/mundial2026` on XAMPP, empty string when deployed at domain root.
- **Auth**: session-based. First registered user becomes admin automatically. Admin PIN hardcoded as `2026` in `config/config.php`.
- **Sessions** stored in `data/sessions/` (persistent volume on Railway).
- **`sql/`** directory is empty and unused.
- **UI language**: Spanish.

## Railway deployment

- **Volume required**: mount a Railway Volume at `/var/www/html/data` for persistent storage. Without it, all user data and sessions are lost on every deploy.
- **Port**: `entrypoint.sh` reads `$PORT` from Railway and reconfigures Apache. Do not hardcode port 80.
- **First deploy**: `entrypoint.sh` copies `teams.json` from `data-init/` backup into the empty volume automatically.
- **`data/teams.json` updates**: if you change `teams.json` in code, the volume still has the old copy. Either delete the volume file manually or add migration logic to `entrypoint.sh`.
- **Build**: Railway uses the `Dockerfile` automatically (`railway.json` sets `builder: DOCKERFILE`).

## Conventions

- Pages live in `pages/`, API endpoints in `api/`, shared PHP in `includes/`, config in `config/`.
- Every PHP file that needs data access requires both `config/config.php` and `includes/functions.php`.
- `config/config.php` calls `session_start()` — do not call it again in files that include config.
