# SPARXSTAR Contracts Registry — Role and Boundary

## Owns

- The published distribution copy of every contract under
  `Contracts/{Group}/{Product}/`.
- `MANIFEST.json` — the authoritative index of contracts (status, paths,
  namespaces, symbols, consumer bindings).
- The Composer package `starisian/sparxstar-contracts-registry` and its PSR-4
  autoload mapping.
- Distribution version tags (cut here, not on source repos).
- The structure/naming gate (`bin/check-contracts.sh`) and the consumer-facing
  conformance gate (`.github/workflows/contract-conformance.yml`).

## Does not own

- Contract source code — owned by each producing product repo (e.g.
  `helios-trust`, the ESU repo). Synced files here are read-only.
- Tech specs — owned by `sparxstar-product-specification-registry`.
- Architecture decisions / invariants — owned by
  `sparxstar-architecture-governance-registry` (ADR-017 governs the canonical
  namespace convention this registry enforces).
- Code-conformance workflows — owned by `sparxstar-code-conformance`.
- PR review against ADRs/specs — owned by `sparxstar-claude-pr-review`. This
  registry holds and dispenses contracts; it does not review them.

## Product group

- Infrastructure (cross-group distribution mirror for IAMC / DVE / IAtlas / Starmus).

## Contracts produced

- None — this repo dispenses contracts authored elsewhere; it does not produce
  its own interfaces.

## Consumed by

- Any repo installing shared interfaces over Composer
  (`composer require starisian/sparxstar-contracts-registry`).
- Repos calling the reusable `contract-conformance.yml` gate.
- Currently bound: `Starisian-Technologies/helios-trust` (per `MANIFEST.json`).
