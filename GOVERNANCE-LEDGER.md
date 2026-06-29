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
