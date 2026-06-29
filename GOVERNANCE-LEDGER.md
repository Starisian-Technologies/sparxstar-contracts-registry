# Governance Ledger

Durable record of open governance items that require owner decision or tracking.

## Open Items

### GL-2026-06-29-001 — Bind DVE Sky-Esu consumers

- **Status:** open
- **Severity:** High
- **Artifact:** `MANIFEST.json` (`contracts.dve/sky-esu`)
- **Why this is open:** `consumers` is currently empty, so
  `dispatch-contract-change.yml` has no downstream targets for Sky-Esu changes.
- **Required decision:** identify and approve the canonical consumer repository
  bindings for `dve/sky-esu`.
- **Authority:** `@MaximillianGroup`
- **Opened:** 2026-06-29

### GL-2026-06-29-002 — Verify dispatch app authority

- **Status:** open
- **Severity:** Critical
- **Artifact:** `.github/workflows/dispatch-contract-change.yml`
- **Why this is open:** cross-repo `repository_dispatch` depends on the
  contract-sync App having the required write authority on each bound consumer
  repository.
- **Required decision:** confirm the installed App permissions and consumer-repo
  installations before treating dispatch as fully verified.
- **Authority:** `@MaximillianGroup`
- **Opened:** 2026-06-29

### GL-2026-06-29-003 — Resolve live ADR/spec registries

- **Status:** open
- **Severity:** Critical
- **Artifact:** `AGENTS.md`, `.github/workflows/copilot-instructions.md`
- **Why this is open:** the architecture-decision and product-spec source
  repositories were not publicly resolvable in this session, so alignment checks
  against those sources must stop and flag uncertainty until the live repository
  identifiers are confirmed.
- **Required decision:** confirm the current repository identifiers for the ADR
  registry and product technical specifications registry, or document that they
  are intentionally private and how agents should access them.
- **Authority:** `@MaximillianGroup`
- **Opened:** 2026-06-29
