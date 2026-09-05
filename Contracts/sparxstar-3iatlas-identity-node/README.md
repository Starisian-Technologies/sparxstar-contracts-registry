# Identity ↔ Key Vault — grant contract

**Status:** draft · **Since:** 1.0.0 · **Enforcement:** INV-017

The one authoritative definition of the `key-vault` credential class: what a
grant carries, which operations exist, how long a destructive grant lives, how
replay is stopped, what the Vault returns, and how an interrupted transaction is
reconciled.

| File | What it is |
| --- | --- |
| `key-vault-grant.schema.json` | Grant claims, with the conditional bindings expressed as schema. |
| `key-vault-receipt.schema.json` | Vault receipt claims. |
| `conformance/vectors.json` | **One** suite of 27 cases, run by both sides. |
| `conformance/README.md` | How each side runs it. |

## Why this file exists

The `key-vault` class was defined abstractly in ADR-020 decision 5 — "the
operation, a transaction ID, the expected manifest version" — while the wire
spellings lived in `docs/AUTH-KEY-LIFECYCLE-INSTRUCTIONS-v2.md`, a document
**hand-copied into two product repositories**. An implementation built from
ADR-020 alone invented field names and an operation vocabulary that were not the
governed ones, and nothing detected it.

The two copies have already drifted: 256 differing lines, measured 2026-09-05.
Appendix B.3 itself is currently byte-identical in both — luck, not structure.
This contract is the single home; both copies become references to it.

## The verified claims

Every Key Vault request **fails closed** unless its token satisfies all of:

- **`aud` is the scalar string `key-vault`.** Never an array — *including* an
  array that contains `key-vault`, which a permissive membership check would
  admit. Session, offline, provisioning and machine tokens are never
  substitutes.
- **`sub` equals `{accountRef}` in the request path.** Exact match, fail closed.
- **`op` matches the route exactly.**
- **`iat`, `nbf` and `exp` are valid.**

## The operations

Eight, closed. Anything else is refused.

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

## Five-minute destructive grants

`exp - iat <= 300` for every destructive operation. 300 is permitted; 301 is
not. Identity must refuse to **mint** a longer one and the Vault must refuse to
**accept** one — a ceiling enforced on only one side is a ceiling one bug away
from not existing.

Read grants may be multi-use within their own short expiry. Only destructive
grants are single-use.

## Single-use `jti`

The Vault persists consumed `jti` values for destructive operations until `exp`,
and **consumes atomically before any mutation**.

Before, not after. A consume-after ordering makes the mutation itself the thing
that races: two concurrent presentations of one grant both pass the check, both
mutate, and the second consume records a fact that is already false.

## Version and transaction binding

`expectedVersion` binds a grant to a manifest that has not moved; a mismatch is
`412`. `txId` is minted with a **durable `PREPARED` record written before the
grant is issued**, so every `txId` the Vault ever sees is one Identity can
classify as committed, safely abortable, or unresolved. A grant issued before
its `PREPARED` record would produce exactly the transaction nobody can resolve.

## Receipts

Signed by the Vault's own keypair, `aud: identity-node`, `exp - iat <= 1800`,
single-use with Identity persisting consumed `jti`s. A rotation receipt attests
a **PENDING** state, never a promotion — promotion happens only through
Identity's server-authenticated confirm. A receipt authorizes nothing except the
one Identity endpoint that consumes it; it is not a bearer credential anywhere.

## Interrupted-transaction reconciliation

The browser is never the only party able to complete a commit.

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

**Invariant at every state:** at least one wrap openable by the user's
Identity-current password or recovery key exists and is promoted or pending. Once
Identity has committed, only promotion — never rollback — can resolve the pending.

## On any failure, nothing moved

Replay, mismatch, expiry or a missing claim: **stored key material is
unchanged.** No partial mutation, no best-effort completion. A verifier that
returns the right status code after having already mutated has failed this
contract even if every vector "passed".

## Provenance

Derived from `AUTH-KEY-LIFECYCLE-INSTRUCTIONS-v2.md` §12 and Appendices B.1,
B.3, B.4 and C.2 — read from the source, not restated from memory. Enforcement
semantics ruled by the platform owner, 2026-09-05, recorded as INV-017.
