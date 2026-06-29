# Copilot Review Instructions — Platform Contracts

This repository holds shared PHP interfaces and contracts for the
Starisian Technologies platform. Files are auto-synced from private
source repos. This repo is a distribution channel, not an authoring
environment.

## Reference repositories (read via MCP)

- **Coding Standards:** `Starisian-Technologies/starisian-technologies-coding-standards`
  — verified public standards repo for naming conventions (ADR-017 SPX
  prefix), namespace rules, and enforcement posture.
- **ADR Registry:** resolve the current Starisian architecture decision record
  repository live in this run before relying on it. If you cannot open it, stop
  and flag uncertainty instead of guessing a repo name.
- **Product Specs:** resolve the current Starisian product technical
  specifications repository live in this run before relying on it. If you
  cannot open it, stop and flag uncertainty instead of guessing a repo name.

## Your role

You review PRs for correctness. Most PRs in this repo are auto-generated
by sync workflows. Manual PRs are rare and should be scrutinized.

## Review rules

- Files under `Contracts/` are auto-synced from source repos. Manual
  edits to synced files will be overwritten — flag any PR that manually
  modifies a synced file.
- The composer.json autoload mapping must match the actual directory
  structure and namespace declarations in the PHP files. Case matters
  on Linux.
- Every interface must use the SPX prefix per ADR-017.
- The canonical namespace is `Starisian\Sparxstar\{ProductName}`.
- No implementation code belongs here. Only interfaces, enums, and
  value objects with no dependencies.

## What you must flag

- Manual edits to auto-synced files
- Namespace mismatches between files and composer.json
- Implementation code (classes with business logic, database calls,
  WordPress dependencies)
- Missing SPX prefix on any interface or type
- A composer.json autoload path that doesn't match the directory case
