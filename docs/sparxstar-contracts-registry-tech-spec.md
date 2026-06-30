---
product_id: sparxstar-contracts-registry
name: "SPARXSTAR Contracts Registry"
status: review
version: "1.0.0"
owner: "@MaximillianGroup"
last_reviewed: "2026-06-30"
spec_id_prefix: "CONTRACTS"
---

# SPARXSTAR Contracts Registry — Technical Specification

---

## 1. Identity

The Contracts Registry is the platform's public distribution mirror for shared
PHP interfaces, enums, and dependency-free value objects. It does not author
contracts and does not review them — it holds the canonical published copy of
each contract so that consuming repositories can install interfaces over
Composer without needing read access to the private source repositories. The
source repository is always the law; this registry is a downstream mirror that
the sync fixes whenever it drifts.

## 2. Role boundary

**Owns:**

- The published distribution copy of every contract under `Contracts/{repo-name}/`
  (one flat folder per source repo, created on first sync via `mkdir -p`).
- `MANIFEST.json` — the authoritative index of contracts, their status, paths,
  namespaces, symbols, and consumer bindings.
- The Composer package `starisian/sparxstar-contracts-registry` and its PSR-4
  autoload mapping.
- Distribution versioning: version tags are cut on this registry, not on source repos.
- The structure/validation gate (`bin/check-contracts.sh`) and the
  consumer-facing conformance gate (`.github/workflows/contract-conformance.yml`).

**Does not own:**

- Contract source code — owned by each producing product repo (e.g.
  `helios-trust` for Helios, the ESU repo for DVE Sky-Esu). Synced files here
  are read-only and overwritten on the next sync.
- Tech specs — owned by `sparxstar-product-specification-registry`.
- Architecture decisions / invariants — owned by
  `sparxstar-architecture-governance-registry`.
- Code-conformance enforcement workflows — owned by `sparxstar-code-conformance`.
- PR review against ADRs/specs — owned by `sparxstar-claude-pr-review`. This
  registry dispenses contracts; it does not review them.

## 3. Platform citations

| Cited                                      | Relevance to this product                                                                                         |
| ------------------------------------------ | ----------------------------------------------------------------------------------------------------------------- |
| ADR-017 — Canonical namespace convention   | Every contract uses `Starisian\Sparxstar\{Product}`; the gate enforces it.                                        |
| INV — Source repo is the law for contracts | Registry is a mirror; the sync fixes drift, never the reverse.                                                    |
| OQ — Remaining ADR/INV citations           | Pending: resolve from `.github/instructions/governance/` once governance-sync has populated it. Not assumed here. |

## 4. Architecture

```text
Contracts/
  {repo-name}/        # one flat folder per source repo (e.g. sparxstar-helios-core)
    README.md
    (interface / enum / value-object files)
MANIFEST.json         # authoritative contract index
composer.json         # PSR-4 autoload, no runtime deps
bin/check-contracts.sh   # structure + naming gate
.github/workflows/    # validate, conformance, dispatch, publish
```

- **Sync-in:** producing repos push their `docs/contracts/*` directly to
  `Contracts/{repo-name}/` on merge to their main, where `{repo-name}` is the
  source repo's own name. The folder is created on first push (`mkdir -p`), so
  the registry needs no advance knowledge of repo names. The registry receives
  files as-is, unedited.
- **Index resolution:** `MANIFEST.json` is the single source workflows read to
  resolve paths, status, and consumer bindings.
- **Distribution:** consumers `composer require starisian/sparxstar-contracts-registry`
  and autoload via the declared PSR-4 roots.
- **Conformance:** `contract-conformance.yml` is a reusable workflow consumers
  call to prove their code honors the pinned contract (IMPLEMENT-ALL, NO-FORK,
  VALID-MEMBER); advisory by default.

## 5. Data model

No database. The data model is `MANIFEST.json`:

| Field                       | Type     | Notes                                                      |
| --------------------------- | -------- | ---------------------------------------------------------- |
| `schema_version`            | string   | Manifest schema version.                                   |
| `status_vocabulary`         | object   | draft / review / ratified / canonical (contract statuses). |
| `binding_filter`            | string[] | Statuses fetchable by consumers (`canonical`, `ratified`). |
| `contracts.{id}.domain`     | string   | Group (IAMC, DVE, …).                                      |
| `contracts.{id}.slug`       | string   | Product slug.                                              |
| `contracts.{id}.path`       | string   | `Contracts/{repo-name}`.                                   |
| `contracts.{id}.status`     | string   | Per `status_vocabulary`.                                   |
| `contracts.{id}.namespaces` | string[] | Declared PHP namespaces.                                   |
| `contracts.{id}.symbols`    | string[] | Fully-qualified interface/enum/VO names.                   |
| `contracts.{id}.consumers`  | string[] | Bound consumer repos.                                      |
| `contracts.{id}.open_items` | string[] | Tracked deviations / follow-ups.                           |

Schema version: `1.0.0`.

## 6. API surface

- **Composer package:** `starisian/sparxstar-contracts-registry`, PHP `>=8.2`,
  PSR-4 roots declared in `composer.json` (currently `SparxStar\\Helios\\` →
  `Contracts/IAMC/Helios/` and `Starisian\\Sparxstar\\Sky\\Contract\\` →
  `Contracts/DVE/Sky-Esu/`).
- **Reusable workflow:** `.github/workflows/contract-conformance.yml`
  (`workflow_call`) — inputs `contract-ref`, `contracts`, `consumer`,
  `consumer-path`, `enforcement_mode`; secret `COMPOSER_RESOLVER_PRIVATE_KEY`.
- **Manifest:** `MANIFEST.json` is the machine-readable resolution surface for
  fetch / dispatch / conformance workflows.
- **Version tags:** distribution tags (`v1.0.0`, moving `v1`) are created here.

No HTTP endpoints, hooks, or events — distribution is via Composer + Git refs.

## 7. Seams

| Connection                     | Direction                                       | Governed by              |
| ------------------------------ | ----------------------------------------------- | ------------------------ |
| Producing repos → registry     | push to main (`sync-contracts`)                 | source repo is the law   |
| Registry → consuming repos     | `composer require` / `contract-conformance.yml` | pinned `contract-ref`    |
| Registry ↔ spec registry       | this repo proposes its own spec via PR          | spec registry is the law |
| Registry ← governance snapshot | `.github/instructions/governance/` (read-only)  | ADR registry             |

## 8. Dependencies

- **Hard:** PHP `>=8.2` (runtime requirement of the published package; no library deps).
- **Soft (dev/CI only):** `markdownlint-cli2`, `prettier` (Node `>=20`) for docs
  linting; not shipped to consumers.
- **Provisionally stubbed:** None.

## 9. Security and privacy

- No secrets, no credentials, no PII in this repo.
- License: proprietary; patent-pending notices retained on synced files.
- Private contract pulls in CI use the `sparxstar-composer-resolver` GitHub App
  (`COMPOSER_RESOLVER_*`); the conformance gate mints a short-lived read token
  and never executes consumer PR code.
- Synced files must never be hand-edited; manual edits are overwritten and can
  silently diverge from the source of truth.

## 10. Current state

**On main:**

- Contracts published for `iamc/helios` (canonical) and `dve/sky-esu`
  (canonical); `iamc/sirus`, `iamc/ouroboros`, `iatlas/*`, `starmus` registered
  at `review` with placeholder READMEs.
- `MANIFEST.json` index, `composer.json` PSR-4 autoload, structure/naming gate,
  and consumer conformance gate all in place and green.
- Gate enforces the SPX prefix (hard) and the canonical namespace (advisory).

**In progress:**

- This tech spec — first submission to the spec registry (status `review`).

**On main (convention):**

- **Flat repo-name layout.** Contracts live at `Contracts/{repo-name}/` and
  specs at `specs/{repo-name}/{repo-name}-tech-spec.md` — each of the ~76 repos
  derives its own path from its own name, with no domain/group lookup and no
  advance registration. Folders are created on first push (`mkdir -p`), so the
  registries never need to know repo names ahead of time.

**Planned:**

- Bind DVE Sky-Esu consumer repositories so dispatch can notify downstreams.
- Pick up upstream namespace fixes (see Open items) once source repos rename.
- Retire legacy nested folders as each source repo re-syncs under the flat scheme.

**Not started:**

- None.

## 11. Open items

Unresolved questions — do not assume answers.

- **Helios namespace deviation:** synced files declare `SparxStar\Helios`, not
  the canonical `Starisian\Sparxstar\Helios`. Fix is upstream in `helios-trust`;
  tracked in `MANIFEST.json` → `iamc/helios.open_items`.
- **DVE Sky-Esu naming:** namespace product segment `Sky` vs slug `Sky-Esu`,
  plus an extra `\Contract` segment. Upstream ESU-repo decision; tracked in
  `MANIFEST.json` → `dve/sky-esu.open_items`.
- **Legacy nested folders:** `Contracts/IAMC/Helios`, `Contracts/DVE/Sky-Esu`,
  and the placeholder entries predate the flat scheme. They are not migrated by
  hand here; each is superseded when its source repo next syncs to
  `Contracts/{repo-name}/`. `composer.json` autoload tracks the actual on-disk
  path until then.
- **Cross-repo sync (informational):** each producing repo's sync
  target (`TARGET_FOLDER` in its `governance.yml`) must change to
  `Contracts/{repo-name}/` in lockstep, or the next sync recreates the old nested
  path. This coordination is outside this registry.
- **Residual dev advisories:** `js-yaml` GHSA-h67p-54hq-rp68 and `markdown-it`
  GHSA-6v5v-wf23-fmfq have no upstream fix yet (dev/CI-only).

## 12. Changelog

| Date       | Version | Change                             |
| ---------- | ------- | ---------------------------------- |
| 2026-06-29 | 1.0.0   | Initial spec submitted for review. |

---

The following stable IDs make this spec promotion-ready (non-binding at
`review`; required before promotion to `canonical`/`ratified`).

## REQ-001 — Faithful distribution mirror

The registry MUST publish each contract exactly as received from its source
repository, with no manual edits to files under `Contracts/**`.

## REQ-002 — Manifest is the resolution authority

All fetch, dispatch, and conformance workflows MUST resolve contract paths,
status, and consumer bindings from `MANIFEST.json`.

## REQ-003 — Naming and namespace convention

Every interface MUST use the `SPX` prefix, and every contract symbol SHOULD use
the canonical `Starisian\Sparxstar\{Product}` namespace (ADR-017); known
deviations are tracked as manifest open items pending upstream fixes.

## AC-001 — Sync overwrites cleanly

Given a source-repo contract change, when the sync runs, then the registry copy
becomes byte-identical to the source and `bin/check-contracts.sh` exits 0.

## AC-002 — Consumers install without source access

Given a consuming repo with no access to a source repo, when it runs
`composer require starisian/sparxstar-contracts-registry`, then it can autoload
the canonical interfaces for any `canonical`/`ratified` contract it is bound to.

## OQ-001 — Canonical namespace deviations

When will `helios-trust` and the ESU repo rename to the canonical namespace so
the advisory clears? Tracked in `MANIFEST.json` open items until resolved.
