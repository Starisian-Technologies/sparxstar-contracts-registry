# Identity ↔ Key Vault — grant contract

**Status:** draft · **Since:** 1.0.0 · **Enforcement:** INV-017

## What Is This?

The one authoritative definition of the `key-vault` credential class: what a
grant carries, which operations exist, how long a destructive grant lives, how
replay is stopped, what the Vault returns, and how an interrupted transaction is
reconciled.

## Why You Need It

The class was defined abstractly in ADR-020 decision 5 — "the operation, a
transaction ID, the expected manifest version" — while the wire spellings lived
in `docs/AUTH-KEY-LIFECYCLE-INSTRUCTIONS-v2.md`, a document **hand-copied into
two product repositories**. An implementation built from ADR-020 alone invented
field names and an operation vocabulary that were not the governed ones, and
nothing detected it.

The two copies have already drifted: 256 differing lines, measured 2026-09-05.
Appendix B.3 itself is currently byte-identical in both — luck, not structure.
This contract is the single home; both copies become references to it.

## At a Glance

| File | What it is |
| --- | --- |
| `key-vault-grant.schema.json` | Grant claims, conditional bindings expressed as schema. |
| `key-vault-receipt.schema.json` | Vault receipt claims. |
| `conformance/vectors.json` | **One** suite of 28 cases, run by both sides. |
| `conformance/check-vectors.mjs` | Reference verifier; proves the suite is self-consistent. |
| `conformance/README.md` | How each side runs it. |

## Core Functionality

Every Key Vault request **fails closed** unless its token satisfies all of:

- **`aud` is the scalar string `key-vault`.** Never an array — *including* an
  array that contains `key-vault`, which a permissive membership check would
  admit. Session, offline, provisioning and machine tokens are never
  substitutes.
- **`sub` equals `{accountRef}` in the request path.** Exact match, fail closed.
- **`op` matches the route exactly.**
- **`iat`, `nbf` and `exp` are present and valid.**

> **Divergence recorded, not silently chosen.** INV-017 requires valid
> `iat`/`nbf`/`exp`. Appendix B.3's claim list omits `nbf`. The owner ruling of
> 2026-09-05 is newer and governs, so `nbf` is **required** here and a grant
> without it is refused rather than treated as valid-from-issue.

## How It Works

Eight operations, closed. Anything else is refused.

| Operation | Destructive | Additional binding |
| --- | :---: | --- |
| `manifest-read` | no | — |
| `manifest-create` | no | — |
| `manifest-rotate` | **yes** | `expectedVersion` **and** `txId` |
| `manifest-provisioning-replace` | **yes** | `expectedVersion` + same `generation` |
| `manifest-reset-read` | no | — |
| `manifest-generation-recover` | no | exact `generation` |
| `device-wrap-create` | **yes** | — |
| `device-wrap-remove` | **yes** | — |

`exp - iat <= 300` for every destructive operation. 300 is permitted; 301 is
not. Identity must refuse to **mint** a longer one and the Vault must refuse to
**accept** one — a ceiling enforced on only one side is one bug away from not
existing. Read grants may be multi-use within their own short expiry.

## Getting Started

Both sides run the same vectors. From `conformance/`:

```sh
node check-vectors.mjs
```

That checks the suite against the contract's own rules. To conform, run
`vectors.json` against your implementation — issuance for Identity,
verification for the Key Vault — honouring `sides` on each case. See
`conformance/README.md`.

## Key Concepts

**Single-use `jti`.** The Vault persists consumed `jti` values for destructive
operations until `exp`, and **consumes atomically before any mutation**. Before,
not after: a consume-after ordering makes the mutation itself the thing that
races, so two concurrent presentations of one grant both pass the check, both
mutate, and the second consume records a fact that is already false.

**Version and transaction binding.** `expectedVersion` binds a grant to a
manifest that has not moved; a mismatch is `412`. `txId` is minted with a
**durable `PREPARED` record written before the grant is issued**, so every
`txId` the Vault sees is one Identity can classify as committed, safely
abortable, or unresolved. A grant issued before its `PREPARED` record produces
exactly the transaction nobody can resolve.

**Receipts.** Signed by the Vault's own keypair, `aud: identity-node`,
`exp - iat <= 1800`, single-use with Identity persisting consumed `jti`s. A
rotation receipt attests a **PENDING** state, never a promotion — promotion
happens only through Identity's server-authenticated confirm. A receipt
authorizes nothing except the one Identity endpoint that consumes it.

## Common Patterns

**Interrupted-transaction reconciliation.** The browser is never the only party
able to complete a commit.

- The Vault **never** expires or rolls back a pending on a timer. A pending
  resolves only by Identity's authenticated confirm (promote), an
  Identity-signed abort stating "no commit recorded for `txId`" (discard), or
  supersession by a new Identity-coordinated attempt.
- **Client dies before submitting the receipt:** nothing is committed anywhere,
  the promoted manifest still matches the password the writer still uses, and
  Identity's reconciler aborts the stale pending — which it can classify for
  *every* transaction precisely because the `PREPARED` record preceded the grant.
- **Client dies after Identity committed:** the durable outbox completes
  promotion without the client, retrying with backoff. A Vault outage delays
  promotion; it never diverges it.

## Important Notes

**On any failure, nothing moved.** Replay, mismatch, expiry or a missing claim:
**stored key material is unchanged.** No partial mutation, no best-effort
completion. A verifier that returns the right status code after having already
mutated has failed this contract even if every vector "passed".

**Invariant at every state:** at least one wrap openable by the user's
Identity-current password or recovery key exists and is promoted or pending.
Once Identity has committed, only promotion — never rollback — can resolve the
pending.

## Related Services

- **Identity Node** (`sparxstar-3iatlas-identity-node`) — the sole issuer.
- **Key Vault** — the sole acceptor. Not yet bound as a consumer in
  `MANIFEST.json`: its repository name was not verified when this contract was
  authored and is deliberately not guessed. The contract is unenforceable on the
  verifier side until it is bound.
- **WordPad** (`sparxstar-3iatlas-wordpad`) — reaches the Vault as a client.

## Support & Questions

Open an issue on this registry. Questions about *why* a rule exists belong with
INV-017 in `sparxstar-architecture-governance-registry`; questions about the
wire itself belong here.

## Licensing

Distributed under this registry's `LICENSE`. Use is limited to Starisian
Technologies platform repositories and partners bound by it; the contract is not
licensed for redistribution outside the platform.

## Versioning

Semantic, tracked by the `since` field of this contract's `MANIFEST.json` entry
and by the registry tag a consumer pins as its `contract-ref`. The manifest
entry is the authoritative record of the current version; the heading above is a
convenience.

Patch and minor releases are backwards compatible: added optional claims,
clarified prose, new conformance vectors that an already-conforming
implementation still passes. **Breaking changes are rare and deliberate** — a
removed or renamed claim, a new required claim, a narrowed operation set, or a
tightened ceiling — and take a major version, an amendment notice to bound
consumers, and a governance ruling. Consumers pin a ref precisely so a breaking
change is adopted rather than delivered.

## Governance

Steward: the Chief Architect, via this registry. Enforcement semantics are
INV-017 in `sparxstar-architecture-governance-registry`; that registry holds
*why*, this contract holds *what*.

Changes arrive as a PR here and require registry-owner approval. A change to the
verified claims, the operation set, the destructive ceiling or the replay rule
additionally requires an owner ruling recorded as an invariant amendment before
this contract is updated — the contract follows the invariant, never the
reverse. Status promotion (`draft` → `review` → `ratified` → `canonical`) is the
registry owner's decision.

## Provenance

Derived from `AUTH-KEY-LIFECYCLE-INSTRUCTIONS-v2.md` §12 and Appendices B.1,
B.3, B.4 and C.2 — read from the source, not restated from memory. Enforcement
semantics ruled by the platform owner, 2026-09-05, recorded as INV-017.
