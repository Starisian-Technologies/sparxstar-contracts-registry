# Key Vault grant — shared conformance vectors

## What Is This?

`vectors.json` is **one suite, run by both sides**. Identity runs it against
grant *issuance*; the Key Vault runs it against grant *verification*.

## Why You Need It

Two suites that happen to agree today are the arrangement this replaces. A
contract is only real where a single set of cases binds issuer and verifier to
the same answers — a suite both sides run cannot be satisfied by two different
guesses. It is also what would have caught the two `docs/` copies drifting.

## At a Glance

| File | What it is |
| --- | --- |
| `vectors.json` | 28 cases — 7 accept, 21 reject. |
| `check-vectors.mjs` | Reference verifier for the suite itself. |

## Core Functionality

Each case carries a `sides` array saying who must run it:

| Side | Meaning |
| --- | --- |
| `issuer` | Identity Node must **refuse to mint** a `reject` case, and must mint an `accept` case. |
| `verifier` | The Key Vault must **refuse to accept** a `reject` case, and accept an `accept` case. |

Cases listing both must hold on both sides.

## How It Works

Every `iat`/`nbf`/`exp` is an absolute second count against `reference_time`. A
runner evaluates each case **as if `now == reference_time`**. Vectors therefore
never expire and never flake — do not regenerate them relative to wall-clock
time, and do not "refresh" the timestamps.

`stored_state` is what the verifier must pretend it holds: `acct-1` at manifest
version 5, generations 1 and 2. Cases such as `reject-version-moved` depend on
it. A case may also carry `consumed_jti`, listing `jti` values already spent.

## Getting Started

```sh
node check-vectors.mjs
```

That runs every vector through the contract's rules and asserts each lands on
the verdict it claims. It tests the *suite*, not an implementation — it exists
so the suite cannot ship self-contradicting. Then run `vectors.json` against
your own side.

## Key Concepts

**The assertion that is not a case.** `universal_assertion` applies to **every**
reject: stored key material is unchanged. No partial mutation, no best-effort
completion. A refused request must be indistinguishable, as far as stored key
material is concerned, from one that never arrived. A verifier that returns the
right status code while having already mutated has failed the suite even though
every case "passed".

## Common Patterns

**Adding a case.** Add it here, in this file, once, then run
`check-vectors.mjs`. A case added on one side only is the defect this contract
exists to prevent.

**A case must fail for its stated reason.** `reject-aud-provisioning` keeps
`op` matching its route deliberately, so the only defect is the audience. A
rejection case with two defects passes for the wrong reason and stops testing
what it names.

## Important Notes

A vector marked `accept` that the contract's own rules reject is a defect in the
vector. `check-vectors.mjs` is what makes that visible before someone tries to
conform against it and cannot.

## Related Services

Identity Node issues; the Key Vault verifies. Both run this suite. See the
contract README one directory up.

## Support & Questions

Open an issue on this registry.

## Licensing

Covered by this registry's `LICENSE`, as part of the contract it belongs to.

## Versioning

Versioned with the contract, not separately. Added vectors are a minor change
that an already-conforming implementation still passes; a vector whose verdict
*changes* is a breaking change to the contract and takes a major version.

## Governance

Changes arrive as a PR on this registry and require registry-owner approval.
`check-vectors.mjs` must pass before merge.
