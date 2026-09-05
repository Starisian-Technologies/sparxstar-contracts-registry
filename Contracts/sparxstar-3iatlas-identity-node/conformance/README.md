# Key Vault grant — shared conformance vectors

`vectors.json` is **one suite, run by both sides**. Identity runs it against
grant *issuance*; the Key Vault runs it against grant *verification*. That is
the whole point: two suites that happen to agree today are the arrangement this
replaces.

## Running it

Each case carries a `sides` array saying who must run it:

| Side | Meaning |
| --- | --- |
| `issuer` | Identity Node must **refuse to mint** a `reject` case, and must mint an `accept` case. |
| `verifier` | The Key Vault must **refuse to accept** a `reject` case, and accept an `accept` case. |

Cases listing both must hold on both sides.

## Fixed time

Every `iat`/`nbf`/`exp` is an absolute second count against `reference_time`.
A runner evaluates each case **as if `now == reference_time`**. Vectors
therefore never expire and never flake — do not regenerate them relative to
wall-clock time, and do not "refresh" the timestamps.

## Fixed stored state

`stored_state` is what the verifier must pretend it holds: `acct-1` at manifest
version 5, generations 1 and 2. Cases such as `reject-version-moved` depend on
it. A case may also carry `consumed_jti`, listing `jti` values already spent.

## The assertion that is not a case

`universal_assertion` applies to **every** reject: stored key material is
unchanged. No partial mutation, no best-effort completion. A refused request
must be indistinguishable, as far as stored key material is concerned, from one
that never arrived. A verifier that returns the right status code while having
already mutated has failed the suite even though every case "passed".

## Adding a case

Add it here, in this file, once. A case added on one side only is the defect
this contract exists to prevent.
