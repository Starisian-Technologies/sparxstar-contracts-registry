<!-- markdownlint-disable MD033 MD036 -->

<img width="1280" height="640" alt="SPARXSTAR Banners-8 (3)" src="https://github.com/user-attachments/assets/f6c7185e-a597-439f-98e6-7fe817eea620" />

# SPARXSTAR Platform Contracts

Starisian Technologies © 2026. All Rights Reserved.

---

## Overview

This repository is the authoritative source for the **Starisian Platform Contract (SPS)** — a platform contract defining the invariants, end-points and boundaries for compliant implementation:

No implementation. No WordPress dependencies. No secrets.

---

## Install

```bash
composer require starisian/sparxstar-contracts-registry
```

## Structure

Contracts are stored flat, one folder per source repository, keyed by the
repository's own name:

```text
Contracts/
  {repo-name}/        # e.g. Contracts/sparxstar-helios-core/
    README.md
    (interface / enum / value-object files)
```

There is no group/domain lookup. Each repository derives its own path from its
own name, so none of the registries need to know repo names in advance. The
first sync from any repo creates `Contracts/{repo-name}/` automatically (the
sync job runs `mkdir -p` before copying).

Each folder is auto-synced from its source repo on merge to main. Do not edit
files here directly — changes will be overwritten on the next sync.

> **Migration note:** some folders predate this convention and are still nested
> under the old group/product layout (e.g. `Contracts/IAMC/Helios/`,
> `Contracts/DVE/Sky-Esu/`). Each will move to `Contracts/{repo-name}/` on its
> source repo's next sync; `composer.json` autoload tracks the actual on-disk
> path until then.

## Usage

```php
use SparxStar\Helios\Contracts\SPXHeliosClientInterface;
use SparxStar\Helios\Consent\SPXConsentReference;
use SparxStar\Helios\Consent\SPXRetentionClass;
use SparxStar\Helios\Consent\SPXConsentTier;
use SparxStar\Helios\Envelope\SPXIamcEnvelope;
use Starisian\Sparxstar\Sky\Contract\SPXEsuInterface;
```

---

## Amendments

All changes to these contracts require:

1. A version increment
2. A published amendment notice
3. A backward compatibility statement

Silent amendment is prohibited. Only Starisian Technologies, or its explicitly designated governance authority, may issue official amendments.

---

---

# Setup & Install — wiring the conformance gate into your CI

This section is for a **consuming repo** adding the contract-conformance gate to its
CI. The gate is the reusable workflow `.github/workflows/contract-conformance.yml` in
this repository. Every value below is read from that file as it stands in this branch.

## 0. Prerequisites (the gate cannot run without these)

The gate's privileged job mints a GitHub **App installation token** (see its
`Mint composer-resolver READ token` step). That means org-level infrastructure must
exist before any consumer can call it.

**GitHub App — `composer-resolver`.** The mint step calls
`actions/create-github-app-token@v3` with `owner: Starisian-Technologies` and
`repositories: sparxstar-contracts-registry`, and the resulting token is used to
**clone this registry** at the pinned ref. So the App must be installed on the
`Starisian-Technologies` org with **Contents: Read** access to
`sparxstar-contracts-registry`. Verify in **Settings → GitHub Apps** that the
`composer-resolver` installation actually includes `sparxstar-contracts-registry` —
an App existing is not the same as it being scoped to that repo, and a missing scope
fails as `repository not found`, not a clear "install the App" message.

**Org variable — `COMPOSER_RESOLVER_CLIENT_ID`.** The mint step reads
`client-id: ${{ vars.COMPOSER_RESOLVER_CLIENT_ID }}`. This is a **Variable**
(`vars.*`), not a secret, and because `create-github-app-token@v3` takes `client-id`,
it holds the App's **client-id string** (not a numeric app-id). It must exist as an
org (or repo) **Variable** in **Settings → Secrets and variables → Actions → Variables**.
A consumer does **not** pass this — it propagates on its own as an org variable.

**Org secret — `COMPOSER_RESOLVER_PRIVATE_KEY`.** The mint step reads
`private-key: ${{ secrets.COMPOSER_RESOLVER_PRIVATE_KEY }}`, and the gate **declares
this in its `secrets:` block as `required: true`**. It must exist as an org/repo
**Secret** (not a Variable), and the consumer passes it by name (Section 4).

> `COMPOSER_RESOLVER_CLIENT_ID` is a **Variable**; `COMPOSER_RESOLVER_PRIVATE_KEY` is a
> **Secret**. They look like a pair but live in different places. A mint pointing at a
> variable that was never created, or a key put in the wrong slot, fails silently.

**Who provisions:** App installation and org variable/secret creation require
org-admin rights. A consuming-repo developer may not have them — if the gate fails
with `repository not found` or an empty-credential error, check these prerequisites
first; the error will not say "you forgot to install the App."

## 1. What this gate does

The gate proves that **this PR's code honors the contract the consumer has pinned**.
On each pull request it fetches the canonical contract fresh from this registry at the
pinned ref (a privileged job that never touches your PR code), then in a separate
unprivileged job it checks out your PR-head and runs static assertions against it —
it reads your code as data and never executes it. It reports IMPLEMENT-ALL (every
canonical interface method is defined), NO-FORK (you didn't re-declare a canonical
symbol locally), and VALID-MEMBER (enum-case / class-const references exist). By
default it warns without blocking.

## 2. The `uses:` line and which ref to pin

The exact reusable-workflow reference a consumer writes:

```yaml
uses: Starisian-Technologies/sparxstar-contracts-registry/.github/workflows/contract-conformance.yml@<ref>
```

**Live tag status:** `v1.0.0` and `v1` both resolve today; `v1.0.0` is the immutable
release pin, while `v1` is the moving major alias currently pointing at the same commit.

- Use `@v1.0.0` for production conformance gating (immutable).
- Use `@v1` only when you intentionally accept movement within major version 1.
- `@main` remains mutable and is for experimentation, not production pinning.

The gate hard-fails unresolvable refs (v3-bug guard), so only pin refs that exist.

## 3. Inputs (from the gate's `on.workflow_call.inputs`)

| Input              | Required | Default                                               | Purpose / valid values                                                                                  |
| ------------------ | -------- | ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| `contract-ref`     | no       | `""` (derived from the `@<ref>` on your `uses:` line) | the registry ref to check against; any tag/branch/SHA string. Explicit value wins over the derived one. |
| `contracts`        | no       | `""` (all you are party to)                           | space/comma list of MANIFEST contract ids to check                                                      |
| `consumer`         | no       | `""` (the caller repo)                                | `owner/repo`; selects which contracts bind                                                              |
| `consumer-path`    | no       | `"."` (whole repo)                                    | subdirectory of your PR-head to check                                                                   |
| `enforcement_mode` | no       | `"advisory"`                                          | `advisory` (warn only) or `gate` (block on failure)                                                     |

None of the inputs are required — the gate runs with an empty `with:` if you pin the
ref on the `uses:` line.

## 4. Secrets the consumer must pass

The gate's `on.workflow_call.secrets` block declares exactly **one** secret:

- **`COMPOSER_RESOLVER_PRIVATE_KEY`** — `required: true`.

Pass it by name:

```yaml
secrets:
  COMPOSER_RESOLVER_PRIVATE_KEY: ${{ secrets.COMPOSER_RESOLVER_PRIVATE_KEY }}
```

**`secrets: inherit` is prohibited** — it forwards every caller secret into the
reusable workflow. Pass only the declared secret, by name.

Note on the mint: the gate passes **only the private key** as a secret. The matching
**`COMPOSER_RESOLVER_CLIENT_ID`** is an **org Variable** (`vars.*`) that the gate reads
on its own — the consumer does **not** pass it. (See Section 0.)

## 5. Required caller setup

- **Trigger: `pull_request` — never `pull_request_target`.** The gate checks your
  PR-head code; on `pull_request` the default checkout is PR-head and the default
  `GITHUB_TOKEN` is read-only. `pull_request_target` runs privileged against the base
  branch and must not be used here.
- **Minimum permissions:** the calling job needs `contents: read` (the gate itself
  declares `permissions: contents: read`; its unprivileged conformance job checks out
  your code with that scope and no token).
- **Consumer-side files/config:** none required. Which contracts bind to you is
  resolved from `MANIFEST.json` in **this registry** (its `consumers` list), or
  narrowed with the `contracts` / `consumer` inputs — there is no spec-declaration file
  or SessionStart hook you must add. If your contract-implementing code lives in a
  subdirectory, point `consumer-path` at it.

## 6. Minimal copy-paste caller

```yaml
# .github/workflows/ci-with-contracts.yml in YOUR repo
name: CI (contracts)

on:
  pull_request:

permissions:
  contents: read

jobs:
  contract-conformance:
    # Substitute the ref once tags exist: pin @v1.0.0. Until then @main is the only
    # resolvable ref (mutable — not for production gating).
    uses: Starisian-Technologies/sparxstar-contracts-registry/.github/workflows/contract-conformance.yml@main
    with:
      enforcement_mode: advisory # advisory (warn) | gate (block) — earn the gate
      # contracts: "iamc/helios"   # optional: restrict to specific MANIFEST ids
      # consumer-path: "src"       # optional: check only a subdirectory
    secrets:
      COMPOSER_RESOLVER_PRIVATE_KEY: ${{ secrets.COMPOSER_RESOLVER_PRIVATE_KEY }}
```

Substitute: the `@<ref>` (use `@v1.0.0` once it is tagged) and, optionally, the
commented inputs. Everything else is read from the live gate.

## 7. Sequencing rule (this gate is called cross-repo)

The gate must **declare** `COMPOSER_RESOLVER_PRIVATE_KEY` in its `secrets:` block
before a consumer passes it, and the owner **re-tags** after any edit so the pinned tag
contains that declaration — otherwise a freshly-wired consumer pinned to an old tag
gets a startup failure (`secret not defined for workflow_call`) even though their
caller is correct.

---

**© 2026 Starisian Technologies. All Rights Reserved.**

**PATENT PENDING**

The technologies, linguistic processing methods, and data structures described in this document are proprietary to **Starisian Technologies** and are the subject of pending patent applications.

This document is furnished for informational purposes only. No license, express or implied, by estoppel or otherwise, to any intellectual property rights is granted by this document. Unauthorized use or reproduction of these technical concepts may result in legal action upon the issuance of related patents.
