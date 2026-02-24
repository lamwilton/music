# Spotify CLI

A full-featured Spotify CLI built on Laravel Zero. 30+ commands for playback control, queue management, discovery, and more — all from your terminal.

**[Docs](https://the-shit.github.io/music/)** · **[Commands](https://the-shit.github.io/music/commands.html)** · **[MCP](https://the-shit.github.io/music/mcp.html)** · **[Vibes](https://the-shit.github.io/music/vibes.html)**

## Quick Start

```bash
composer global require the-shit/music

spotify setup      # Configure Spotify API credentials
spotify login      # Authenticate via OAuth
spotify play "Killing In the Name"
spotify current    # See what's playing
spotify player     # Launch interactive TUI player
```

## Highlights

- **Playback** — play, pause, skip, volume, shuffle, repeat
- **Queue** — add tracks, view upcoming, auto-fill from recommendations
- **Discovery** — search, mood queues (chill / flow / hype), top tracks
- **Interactive Player** — TUI with progress bar and keyboard controls
- **Daemon** — background playback via spotifyd, macOS media key integration
- **Integrations** — Slack sharing, webhooks, event streaming

See the full [command reference](https://the-shit.github.io/music/commands.html) for all 32 commands.

## MCP Server

This CLI doubles as an [MCP server](https://the-shit.github.io/music/mcp.html) — AI assistants like Claude can control your Spotify directly. 12 tools for playback, queue, search, and more.

```json
{
  "mcpServers": {
    "spotify": {
      "command": "spotify",
      "args": ["mcp:start", "spotify"]
    }
  }
}
```

See the [MCP setup docs](https://the-shit.github.io/music/mcp.html) for Claude Desktop, Claude Code, and OpenCode configuration.

## Vibe Check

Every commit must include the Spotify track playing when the code was written. A pre-commit hook injects the track URL, and CI rejects any push without one.

The result is the [vibes page](https://the-shit.github.io/music/vibes.html) — a living soundtrack of the entire codebase.

## Requirements

- PHP 8.2+
- Composer
- Spotify Premium account
- A [Spotify Developer](https://developer.spotify.com/dashboard) application

## License

MIT
