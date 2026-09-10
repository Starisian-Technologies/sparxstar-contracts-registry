# SETUP — consuming the Contracts Registry

**What this repo is.** The canonical home of shared PHP interface contracts —
the seams two or more repos must both honour. Contracts are **auto-synced from
their source repos**, versioned, and carry a ratification status.

**Access.** Public — no auth needed to *call* its reusable workflows, but the
workflows themselves mint tokens to read private sources, so consumers still
pass the resolver key.

> **New repo?** The `starisian-technologies-proprietary-license` template is
> gaining a setup checklist (`GOVERNANCE-SETUP.md`) that sequences all five
> governance repos, plus a pre-wired caller — both on that repo's open
> governance-wiring PR, **not yet on its default branch**. Check there first;
> this file documents *this* repo's interface only.

---

## Which registry holds what

Easy to get wrong, and the two are not interchangeable:

| | This repo | Architecture Governance Registry |
|---|---|---|
| Holds | **Executable interface contracts** — the PHP interfaces a consumer compiles against | **Contract documents** — the prose contracts in `contracts/`, distributed as `contracts.compiled.md` |
| Answers | "What is the exact signature I must implement?" | "What does this seam mean, and why?" |

Both are real and both are needed. If you are reading a `.md` describing a
seam, that came from the ADR registry's sync. If you are implementing an
`interface`, it came from here.

---

## `contract-conformance.yml` — check your code against the contracts

Reusable. The main gate a consumer wires up.

```yaml
  contracts:
    uses: Starisian-Technologies/sparxstar-contracts-registry/.github/workflows/contract-conformance.yml@v1.0.2
    with:
      consumer: <your-repo-name>
      enforcement_mode: advisory
    secrets:
      COMPOSER_RESOLVER_PRIVATE_KEY: ${{ secrets.COMPOSER_RESOLVER_PRIVATE_KEY }}
```

| Input | Default | Notes |
|---|---|---|
| `contract-ref` | `''` | Registry ref to check against |
| `contracts` | `''` | Limit to named contracts |
| `consumer` | `''` | Your repo, for per-consumer contract selection |
| `consumer-path` | `.` | Where your code is |
| `enforcement_mode` | `advisory` | Advisory first; `gate` once clean |

`COMPOSER_RESOLVER_PRIVATE_KEY` is **required**.

Unlike `adr-enforce.yml` in the ADR registry, this one already defaults to
`advisory` — adopting it cannot block a team on day one.

## `fetch-contracts.yml` — pull contracts into your CI

| Input | Required | Default |
|---|---|---|
| `contract-ref` | **yes** | — |
| `agent-ref` | no | `''` |
| `release-ref` | no | `''` |
| `contracts` | no | `''` |
| `consumer` | no | `''` |
| `report-drift` | no | `false` |
| `report-gate` | no | `advisory` |
| `artifact-name` | no | `fetched-contracts` |

Secrets: `COMPOSER_RESOLVER_PRIVATE_KEY` **required**;
`CONTRACT_SYNC_PRIVATE_KEY` optional, and only when `report-drift` writes back.

## `propose-contract.yml` — propose a contract change back

Opens a PR against this registry. Mints a **write** token, so
`CONTRACT_SYNC_PRIVATE_KEY` is required.

| Input | Required | Default |
|---|---|---|
| `product` | **yes** | — |
| `contract-content` | **yes** | — |
| `source-repo` | **yes** | — |
| `source-sha` | **yes** | — |
| `contract-path` | no | `''` |
| `status` | no | `review` |
| `pr-body-extra` | no | `''` |
| `draft-pr` | no | `true` |

Contracts are **synced from source repos**, so the durable fix for a wrong
contract is almost always in the source repo, not here. Editing the synced copy
directly is overwritten on the next sync.

---

## Installing the published packages

Some contracts ship as private Composer packages. Configure Composer auth with
a minted read token **before** `composer install`; the exact block is in the
Cross-Repo Access Standard (product-spec registry). Do not restate it in your
repo.

`sparxstar-ouroboros-integrity` is a **published, complete package**. It is not
pending and not unpublished. Any `packages/` stub directory left in a consumer
repo is obsolete scaffolding — delete it and install the real package through
the documented auth path.

There is **no** `wordpress/mcp-adapter` package on Packagist. It does not
exist; it was invented by an AI and has been referenced in this org before.
Never add it.

---

## Tags

Read from the live remote 2026-09-10: `v1.0.0`, `v1.0.1`, `v1.0.2`, and the
moving `v1` alias. Pin an immutable patch tag, never `v1` and never `main`.

---

## Credentials

Defined once, in the Cross-Repo Access Standard:

> `sparxstar-product-specification-registry` →
> `specs/_platform/SPARXSTAR-CROSS-REPO-ACCESS-STANDARD.md`

Read: composer-resolver. Write: contract-sync. No PATs, ever. Pass named
secrets; never `secrets: inherit`; never pass a write key to a read-only job.
Do not restate the mint block here.

---

## Related

| You need | Go to |
|---|---|
| Wiring a brand-new repo, end to end | `starisian-technologies-proprietary-license` → `GOVERNANCE-SETUP.md` — *landing on that repo's open governance-wiring PR; not yet on its default branch* |
| Cross-repo auth | product-spec registry → `SPARXSTAR-CROSS-REPO-ACCESS-STANDARD.md` |
| ADRs, invariants, contract *documents* | `sparxstar-architecture-governance-registry` → `SETUP.md` |
| Lint/style enforcement | `sparxstar-code-conformance` → `REUSABLE-WORKFLOWS.md` |
| AI PR review | `sparxstar-claude-pr-review` → `SETUP.md` |
