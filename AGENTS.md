# Agent Instructions — Platform Contracts

## Critical rule

DO NOT EDIT FILES IN THIS REPO.

Almost all files in `Contracts/` are auto-synced from private source
repos by the `sparxstar-contract-sync` workflow. Any edit you make will
be overwritten on the next sync. If an interface needs to change, change
it in the SOURCE REPO (Helios, ESU, Sirus, etc.) and let the sync
propagate the change.

## What agents may do

- Update the root README.md
- Update the root composer.json (autoload mappings, package metadata)
- Fix sync workflow issues
- Add new product folders (with README placeholders) when a new product
  begins publishing contracts

## What agents must NOT do

- Edit any PHP file under Contracts/. These are synced.
- Add implementation code. This repo holds interfaces only.
- Change namespace conventions. The canonical namespace is set by ADR-017.
- Tag releases without owner approval.
