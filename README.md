# Ananya

This repository has two audience-specific guides.

## Start Here

- End users: [README - EndUsers.md](README%20-%20EndUsers.md)
- New developer teams: [README - NewDevsStartHere.md](README%20-%20NewDevsStartHere.md)

## Blocklist Configuration

Puzzle safety blocklists are file-based and editable without PHP code changes.

- Location: `config/blocklists/`
- Moderation files: `moderation_english.txt`, `moderation_telugu.txt`, `moderation_hindi.txt`, `moderation_gujarati.txt`, `moderation_malayalam.txt`
- Theme files: `themes_english.txt`, `themes_telugu.txt`, `themes_hindi.txt`, `themes_gujarati.txt`, `themes_malayalam.txt`

Rules:

- Use UTF-8 encoding.
- Add one entry per line.
- Empty lines are ignored.
- Lines starting with `#` are treated as comments.
- Changes are picked up on the next request (no restart required).

Safety mode:

- If any required blocklist file is missing, unreadable, or empty after parsing, puzzle generation is blocked (fail-closed).

## 🚀 Quick Start

- Main app: <https://ananya.telugupuzzles.com>
- Local app: <http://localhost/ananya/>
- API docs: <http://localhost/ananya/docs/api.php>
- Swagger explorer: <http://localhost/ananya/docs/swagger.php>

## Project Notes (Maintainers)

- Refactoring notes: [REFACTORING_README.md](REFACTORING_README.md)
- Validation response update notes: [VALIDATION_RESPONSE_UPDATES.md](VALIDATION_RESPONSE_UPDATES.md)
