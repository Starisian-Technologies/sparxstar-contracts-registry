<img width="1280" height="640" alt="SPARXSTAR Banners-8 (3)" src="https://github.com/user-attachments/assets/f6c7185e-a597-439f-98e6-7fe817eea620" />

# SPARXSTAR Platform Contracts

Starisian Technologies © 2026. All Rights Reserved.

---

## Overview

This repository is the authoritative source for the **Starisian Platform Contract (SPS)** — a platform contract defining the invariants, end-points and boundaries for compliant implementation:

No implementation. No WordPress dependencies. No secrets.

---

## Install

bash

```
composer require starisian/sparxstar-platform-contracts
```

## Structure

```
src/
  Helios/           # Identity, consent, retention
  Sirus/            # Context, trust, authority (when added)
  Ouroboros/         # Integrity, signing (when added)
```

Each folder is auto-synced from its source repo on merge to main. Do not edit files here directly --- changes will be overwritten on the next sync.

## Usage

php

```
use SparxStar\Contracts\Helios\SPXHeliosClientInterface;
use SparxStar\Contracts\Helios\SPXConsentReference;
use SparxStar\Contracts\Helios\SPXRetentionClass;
use SparxStar\Contracts\Helios\SPXConsentTier;
use SparxStar\Contracts\Helios\SPXIamcEnvelope;
```

---

## Amendments

All changes to thse contracts require:

1. A version increment
2. A published amendment notice
3. A backward compatibility statement

Silent amendment is prohibited. Only Starisian Technologies, or its explicitly designated governance authority, may issue official amendments.

---

---

# Contracts Registry — setup (Sections 0–8)

This registry distributes cross-repo contracts the way the spec registry distributes
specs: it is the single canonical source, consumers **pull fresh every run** (never
hold a stale contract), product repos **propose** contracts in via PR, and consumers
**prove conformance** against the contract they pulled. Four reusable/registry
workflows do this. Every value below is read from this repo's own live workflows.

## 0. Prerequisites

**Two GitHub Apps** (provisioned by the org owner, installed at the org level):

| App | Direction | Used by | Client ID (Variable) | Private key (Secret) |
|---|---|---|---|---|
| **composer-resolver** | READ + cross-repo dispatch | fetch-contracts, contract-conformance, dispatch-contract-change | `COMPOSER_RESOLVER_CLIENT_ID` | `COMPOSER_RESOLVER_PRIVATE_KEY` |
| **contract-sync** | WRITE into this registry | propose-contract, fetch-contracts drift report-back | `CONTRACT_SYNC_CLIENT_ID` | `CONTRACT_SYNC_PRIVATE_KEY` |

**Secrets vs Variables — do not mix them up.** The `*_CLIENT_ID` values are
**Variables** (`vars.*`, non-secret); the `*_PRIVATE_KEY` values are **Secrets**
(`secrets.*`). Putting a private key in a Variable exposes it.

**App-scoping warning.** A minted App token is scoped to specific `repositories:`.
fetch/conformance scope composer-resolver to **this** registry; dispatch scopes
composer-resolver to the **target consumer** repos (the App must be installed there
with permission to receive `repository_dispatch`). Over-scoping a token is a
least-privilege violation — scope to exactly the repos a job touches.

**Who provisions:** the registry/org owner creates both Apps, installs them, and sets
the org-level Variables and Secrets. Consumers only reference them by name.

## 1. What each workflow does

- **fetch-contracts.yml** (reusable) — pull a fresh copy of the contract(s) you are
  party to at a pinned `contract-ref`. Uploads an artifact (retention 1 day). Never
  store a contract locally. Three-ref model, MANIFEST-driven paths, version-policy
  floor + runtime-resolved recommended + v3-bug guard, DO-NOT-EDIT headers.
- **contract-conformance.yml** (reusable) — prove THIS PR's code honors the pinned
  contract. Two-job split: privileged job fetches the pinned contract (read token,
  never touches PR-head); unprivileged job checks out PR-head and runs assertions
  (no token, reads code as data). Advisory by default.
- **propose-contract.yml** (reusable) — propose a contract into this registry; opens a
  draft PR. Never merges, never promotes status to canonical/ratified.
- **dispatch-contract-change.yml** (registry-side) — on a contract merge to main,
  notifies bound consumers (repository_dispatch) so they re-run conformance.

Registry-side support: **validate-contracts.yml** (PR gate on contract structure),
**collect-drift.yml** (rebuilds `drift/INDEX.md`), **publish-contracts.yml**
(tag → Release).

## 2. Pin the version

Always pin an immutable tag on the `uses:` line. `@v1.0.0` is the documented consumer
pin; `@v1` is a moving alias on the same commit.

```yaml
uses: Starisian-Technologies/sparxstar-platform-contracts/.github/workflows/contract-conformance.yml@v1.0.0
```

## 3. Inputs (from the live `on.workflow_call.inputs`)

**contract-conformance.yml**

| Input | Required | Default | Meaning |
|---|---|---|---|
| `contract-ref` | no | derived from the `@<ref>` you pinned | the contract version you adopted (checked, not main) |
| `contracts` | no | all you are party to | space/comma list of MANIFEST contract ids |
| `consumer` | no | the caller | owner/repo; selects which contracts bind |
| `consumer-path` | no | `.` | subdir of your PR-head to check |
| `enforcement_mode` | no | `advisory` | `advisory` (warn) or `gate` (block) |

**fetch-contracts.yml**: `agent-ref`, `contract-ref` (required), `release-ref`,
`contracts`, `consumer`, `report-drift`, `report-gate`, `artifact-name`.

**propose-contract.yml**: `product` (required), `contract-path`, `contract-content`
(required), `status` (default `review`), `source-repo` (required), `source-sha`
(required), `pr-body-extra`, `draft-pr` (default `true`).

## 4. Secrets — by name, `inherit` prohibited

Pass secrets explicitly by name. **Do not** use `secrets: inherit` — it leaks every
caller secret into the reusable workflow.

| Workflow | Required secret | Optional |
|---|---|---|
| contract-conformance.yml | `COMPOSER_RESOLVER_PRIVATE_KEY` | — |
| fetch-contracts.yml | `COMPOSER_RESOLVER_PRIVATE_KEY` | `CONTRACT_SYNC_PRIVATE_KEY` (only with `report-drift`) |
| propose-contract.yml | `CONTRACT_SYNC_PRIVATE_KEY` | — |

## 5. Caller setup — `pull_request`, NOT `pull_request_target`

The conformance gate checks your **PR-head** code. Trigger on `pull_request` so the
default checkout is PR-head and the default token is read-only. `pull_request_target`
runs with write privileges against the base — never use it for this.

## 6. Advisory-first / earn-gate

`enforcement_mode` defaults to **advisory**: violations are reported as warnings and
never block. Flip to **gate** only once a repo is clean and wants to stay clean —
earn the gate. (A contract moving ahead of your pin is expected and never a failure;
the gate checks the pinned contract against your PR-head, not main against main.)

## 7. Copy-paste caller

```yaml
# .github/workflows/ci-with-contracts.yml in YOUR repo
name: CI (contracts)
on:
  pull_request:
permissions:
  contents: read
jobs:
  contract-conformance:
    uses: Starisian-Technologies/sparxstar-platform-contracts/.github/workflows/contract-conformance.yml@v1.0.0
    with:
      contract-ref: v1.0.0
      enforcement_mode: advisory   # flip to gate once clean
    secrets:
      COMPOSER_RESOLVER_PRIVATE_KEY: ${{ secrets.COMPOSER_RESOLVER_PRIVATE_KEY }}
```

More templates in [`templates/calling-repo/`](templates/calling-repo/).

## 8. Sequencing rule (registry-first)

After ANY edit to a reusable workflow's secrets/inputs/logic, **re-tag** onto the
commit with the edit (move `@v1.0.0`/`@v1`), **then** consumers swap their pin. The
registry changes first; consumers follow. Never change a reusable workflow's contract
without re-tagging — pinned consumers would silently run old logic.

---

**© 2026 Starisian Technologies. All Rights Reserved.**

**PATENT PENDING**

The technologies, linguistic processing methods, and data structures described in this document are proprietary to **Starisian Technologies** and are the subject of pending patent applications.

This document is furnished for informational purposes only. No license, express or implied, by estoppel or otherwise, to any intellectual property rights is granted by this document. Unauthorized use or reproduction of these technical concepts may result in legal action upon the issuance of related patents.
