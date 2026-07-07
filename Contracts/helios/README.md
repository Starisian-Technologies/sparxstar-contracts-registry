# Helios Contracts

Shared PHP interfaces and value objects that Helios publishes for platform
consumers (ESU, DVE, Sky, Dheghom, 3iAtlas). Zero WordPress dependencies — any
PHP service can type-check against these without installing WordPress.

**This directory is the source of truth.** The code in this repo is what runs.
These files are mirrored to `Contracts/IAMC/Helios/` in
`sparxstar-contracts-registry` by the contract-sync job in
`.github/workflows/governance.yml`. Other repos read the registry copy because
they cannot see this source. If the registry and this directory disagree, this
directory wins — the next sync corrects the registry.

Composer-installable as `starisian/sparxstar-helios-contracts`
(`SparxStar\Helios\` PSR-4, rooted at this directory). When adding or changing
an interface, update this README in the same change.

Platform rules these contracts enforce: identity originates only in Helios and
crosses service boundaries solely as the opaque `contributor_ref`
(ADR-012 / INV-010); retention is fail-closed (INV-009).

## Interfaces

### `Contracts/SPXHeliosClientInterface`

The authoritative contract for Helios identity, device, trust, and session
operations. Every enforcement point depends on this interface, never on a
concrete class.

| Method                                           | Returns                                                                                                            |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------ |
| `evaluate(proof, pulse, sensitivity, now, zone)` | `AgreementResult` — exactly one of `ALLOW_EDGE`, `ALLOW_ORIGIN`, `STEP_UP`, `DENY` (PAM-001 §8, Two-Zone Rule §7). |
| `getIdentityContext()`                           | `SPXHeliosIdentityDataInterface` — throws if no authenticated user; call `validateSession()` first.                |
| `getDeviceContext()`                             | `array{device_hash, source, version}` — `source` is `helios` or `sirus`; callers never generate hashes.            |
| `getTrustState()`                                | `string` — one of `TrustEngine::STATE_NORMAL`, `STATE_STEP_UP_REQUIRED`, `STATE_LOCKED` (read-only projection).    |
| `validateSession()`                              | `bool` — JWT + DB-row + device-hash checks; returns `false` (never throws) on any failure.                         |
| `requireStepUp()`                                | `void` — transitions trust state to `STEP_UP_REQUIRED` and revokes the current token.                              |

### `Contracts/SPXSirusClientInterface`

Contract for consuming context and signals from the Sirus Context plugin. Helios
consumes from Sirus; it never writes to Sirus. Implementations must not mutate
session or trust state, and must not provide identity.

| Method               | Returns                                                                                                                                               |
| -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| `getDeviceContext()` | `array{device_hash, source, version}` — `source` always `sirus`.                                                                                      |
| `getSignals()`       | `array<string,mixed>` — opaque to Helios (interpreted by TrustEngine); keys include `risk` (0–100), `threat` (bool), `reasons` (string[]), `version`. |

### `Identity/SPXHeliosIdentityDataInterface`

The only identity surface any platform service may consume. The WordPress
`user_id` / account id never leaves the Helios auth layer (ADR-012 / INV-010);
consumers receive only the opaque `contributor_ref`.

| Method                | Returns                                                                                       |
| --------------------- | --------------------------------------------------------------------------------------------- |
| `getContributorRef()` | `string` — UUID v4, or empty string for anonymous contexts.                                   |
| `getCorrelationId()`  | `string` — equals `session_id` when a session exists, else a fresh UUID; propagate in traces. |
| `getRoles()`          | `string[]` — opaque role strings (do not assume WordPress slugs).                             |
| `getVersion()`        | `string` — semver-style schema version of the identity object.                                |
| `getIssuedAt()`       | `int` — Unix timestamp the context was created.                                               |
| `isAnonymous()`       | `bool` — true when unauthenticated (empty `contributor_ref`).                                 |

## Value objects

### `Envelope/SPXIamcEnvelope`

Immutable IAM+C envelope — the single serialized identity/access/consent/context
packet issued by Helios and Sirus together. Constructed only by
`SPXIamcEnvelopeBuilder` inside the plugin; consumers type-check against this
class. `to_array()` is wire-safe (REST + inter-service) and contains no
real-world identity. Public projections use `release_author_ref` (non-correlating
per release; ADR-012 amendment / GAL), never `contributor_ref`.

Helpers: `is_vault()`, `is_anonymous()`, `has_threat()` (fail-closed `false` when
Sirus absent), `risk_score()` (0 when Sirus absent).

### `Consent/SPXConsentReference`

Immutable snapshot of a resolved consent decision returned by `ConsentResolver`
and serialized by `/helios/v1/consent/resolve`. Exposes `consent_id` (opaque
UUID), `technical_consent` (bool), per-purpose consent map (`PURPOSE_STORAGE`,
`PURPOSE_TRAINING`, …), the derived `retention_class`, and `resolved_at`.
`ephemeral_null()` yields a fail-closed reference (EPHEMERAL, all flags false)
when no consent record exists.

## Enums

| Enum                                             | Cases                             | Meaning                                                                                                                                                                                                                                     |
| ------------------------------------------------ | --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Agreement/Enums/SPXResourceSensitivity` (`int`) | `LEVEL_1`, `LEVEL_2`, `LEVEL_3`   | Drives the Two-Zone Rule. L1 may be served at the edge (`ALLOW_EDGE`); L2/L3 may only be finally allowed at origin; L3 always requires step-up and must not use email 2FA. _Provisional — removed once Ouroboros ships the canonical type._ |
| `Consent/SPXConsentTier` (`string`)              | `ADULT`, `MINOR`, `INSTITUTIONAL` | Consent authority model. MINOR/INSTITUTIONAL require confirmed external consent before VAULT (OQ-012). `default()` → ADULT.                                                                                                                 |
| `Consent/SPXRetentionClass` (`string`)           | `VAULT`, `EPHEMERAL`              | Carrier retention outcome (ADR-013 / INV-009). VAULT requires `technical_consent` + `purposes['storage']`; everything else fails closed to EPHEMERAL via `fail_closed()`.                                                                   |
