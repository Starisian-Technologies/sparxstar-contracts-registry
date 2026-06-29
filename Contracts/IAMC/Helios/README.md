# Helios — Identity, Access & Consent Contracts

## What Is This?

Helios is the SPARXSTAR identity and access management contract domain. It defines the invariants for identity verification, device context, trust-state transitions, consent authority, and retention governance across all platform services.

## At a Glance

| Property          | Value                                 |
| ----------------- | ------------------------------------- |
| Status            | `canonical`                           |
| Since             | 1.0.0                                 |
| PSR-4 root        | `SparxStar\Helios\`                   |
| Binding consumers | `Starisian-Technologies/helios-trust` |

## Contract Surface

Synced Helios files declare concrete sub-namespaces under the
`SparxStar\Helios\` PSR-4 root, including `Contracts`, `Consent`, `Envelope`,
`Identity`, and `Agreement\Enums`. Import the concrete FQCN that matches the
file you consume.

| Contract                         | Kind         | Purpose                                                                             |
| -------------------------------- | ------------ | ----------------------------------------------------------------------------------- |
| `SPXHeliosClientInterface`       | Interface    | Single shared contract for all Helios operations (identity, device, trust, session) |
| `SPXSirusClientInterface`        | Interface    | Sirus authority contract consumed by Helios                                         |
| `SPXHeliosIdentityDataInterface` | Interface    | Immutable cross-service identity projection                                         |
| `SPXIamcEnvelope`                | Value object | IAMC request envelope                                                               |
| `SPXConsentTier`                 | Enum         | Consent authority model (ADULT / MINOR / INSTITUTIONAL)                             |
| `SPXConsentReference`            | Value object | Immutable resolved consent decision snapshot                                        |
| `SPXRetentionClass`              | Enum         | Data retention governance class                                                     |
| `SPXResourceSensitivity`         | Enum         | Resource sensitivity classification for access evaluation                           |

## Rules

- No identity originates outside Helios.
- Device context is consumed, not generated, by callers.
- Trust-state transitions happen exclusively inside TrustEngine.
- Every enforcement point calls `validateSession()` before any identity or access operation.

## Important Notes

These files are synchronized from the `helios-trust` source repository. Do not edit them directly — changes are overwritten on the next sync. Propose changes via `propose-contract.yml` in the source repository.

## Related Contracts

- [IAMC/Sirus](../Sirus/README.md) — authority and context resolution
- [IAMC/Ouroboros](../Ouroboros/README.md) — integrity and signing

## Support & Questions

See the repository [SECURITY.md](../../../../SECURITY.md) for vulnerability reports and [SUPPORT.md](../../../../SUPPORT.md) for usage questions.
