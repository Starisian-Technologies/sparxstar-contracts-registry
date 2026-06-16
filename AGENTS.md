---
applyTo: '**'
---

# SPARXSTAR Platform Contracts — Agent Architecture

## Repository Purpose

This repository is the **authoritative, versioned contract layer** for the Starisian Technologies SPARXSTAR platform. It defines the PHP interfaces and data contracts that all platform services must implement.

## Key Principles

### ✓ Do This
- **Read** interfaces to understand platform architecture
- **Reference** these contracts in your code and documentation
- **Request changes** through the governance process (see Amendments below)
- **Write consumers** that implement these interfaces correctly

### ✗ Do NOT Do This
- **Edit files directly** — contracts are auto-synced from source service repositories
- **Add implementation code** — this repo contains only interfaces and contracts
- **Include dependencies** — contracts must be pure PHP with no external dependencies
- **Store state** — contracts define behavior, not storage

## Auto-Sync Architecture

Each contract directory corresponds to a source service repository:

| Contract | Source Repo | Syncs From |
|----------|------------|-----------|
| `/Contracts/DVE/**` | sparxstar-dve | Digital Voice Engine services |
| `/Contracts/IAMC/**` | sparxstar-iamc | Identity, Auth, Messaging, Context |
| `/Contracts/IAtlas/**` | sparxstar-iatlas | Data structures and orchestration |
| `/Contracts/Starmus/**` | sparxstar-starmus | Audio service contracts |

When a source repository updates its contracts, this repo receives a notification via GitHub Actions and merges the changes automatically. **Never edit these files locally** — your changes will be overwritten on the next sync.

## Contract Amendments

All breaking or significant changes to contracts require:

1. **Version Increment** — Follow semantic versioning
2. **Amendment Notice** — Published summary of changes
3. **Compatibility Statement** — Backward compatibility strategy
4. **Governance Approval** — Review by Starisian Technologies

## For Consumers (Integrators)

When building against these contracts:

1. **Use `composer require starisian/sparxstar-platform-contracts`** to pull the latest version
2. **Import only interfaces** — never import concrete implementations
3. **Implement interfaces completely** — partial implementation will fail in production
4. **Test against the contract** — your code should work with any implementation

## For Service Owners

If you're maintaining one of the source services and need to update a contract:

1. Edit the interface in your service repository
2. Tag a release with semantic versioning
3. Submit a pull request to sync to this repository
4. Include amendment documentation
5. Wait for governance review before merging

## Governance & Licensing

- **© 2026 Starisian Technologies** — All Rights Reserved
- **PATENT PENDING** — Technologies and methods are proprietary
- **GPL-2.0-or-later** — Composer package license
- **Breaking Changes** — Require explicit governance approval

## Questions?

If you're unsure whether to edit a file or how to use a contract, **ask in the repository issues** before making changes.
