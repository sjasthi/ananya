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

## Project File Structure (Current)

This is the current workspace layout grouped by runtime role.

```text
ananya/
	index.php
	chat.php
	chat_api.php
	api.php
	analyzer.php
	analyze.php
	finder.php
	bulk_puzzles.php
	api_testing.php
	play_with_telugu.php
	about.php
	word_processor.php
	telugu_parser.php
	hindi_parser.php
	gujarati_parser.php
	malayalam_parser.php
	api/                     # endpoint handlers routed by api.php
	includes/                # shared runtime modules
		api_reference.php
		header.php
		llm_handler.php
		blocklist_loader.php
	config/
		blocklists/            # moderation and theme text lists
	js/                      # page scripts
		analyzer.js
		chat.js
		bulk_puzzles.js
		finder.js
		index.js
	css/
	docs/
	images/
	mcp_server/              # optional tool-orchestrated chat service
	scripts/                 # developer checks and utilities
	src/                     # refactor library source (not in active API path)
	test_data/               # test fixtures and sample input themes
	unused/                  # archived legacy files
```