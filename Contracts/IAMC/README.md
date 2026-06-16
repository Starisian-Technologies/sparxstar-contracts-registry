# SPARXSTAR Identity, Auth, Messaging & Context (IAMC)

## What Is This?

IAMC is the **security and trust foundation** of the SPARXSTAR platform. It provides:

- **Helios**: Identity verification, consent management, and session trust
- **Sirus**: Device context, request authority, and network awareness
- **Ouroboros**: Digital signatures, integrity verification, and audit trails

No transaction, no data operation, no voice call happens without IAMC validation. Every request validates through IAMC before reaching the core platform.

## Why You Need It

Security isn't optional. IAMC ensures:

- **User identity is verified** — You know who's making requests
- **Consent is tracked** — Users agree to what you're doing
- **Device context is understood** — You know where requests come from
- **Authority is validated** — Only authorized requests proceed
- **Audit trails are immutable** — Compliance and forensics ready

If you're building compliant, production-grade applications, IAMC is your foundation.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Status** | Production-ready |
| **Audience** | Enterprise applications, compliance-focused systems |
| **Key Services** | Helios, Sirus, Ouroboros |
| **Dependencies** | Redis, audit storage |
| **Versioning** | Semantic; breaking changes rare |

## Core Capabilities

### Identity (Helios)
- **Session validation** — Verify user sessions are valid and current
- **Consent management** — Track what users have agreed to
- **Retention policies** — Enforce data deletion schedules
- **Agreement tracking** — Know which versions users accepted
- **Multi-factor support** — Integrate MFA workflows

### Context & Authority (Sirus)
- **Device fingerprinting** — Understand device characteristics
- **Network awareness** — Detect location, connection type, quality
- **Trust scoring** — Assess request trustworthiness
- **Authority enforcement** — Only let authorized requests through
- **Anomaly detection** — Flag unusual request patterns

### Integrity (Ouroboros)
- **Digital signatures** — Sign critical data structures
- **Integrity verification** — Prove data hasn't been tampered
- **Audit trail** — Immutable log of who did what when
- **Non-repudiation** — Signatories can't deny their actions

## How It Works

Every request flows through IAMC:

```
User makes request
	↓
[Helios] validates identity + session
	↓
[Helios] checks consent for this operation
	↓
[Sirus] captures device + network context
	↓
[Sirus] scores request trustworthiness
	↓
[Sirus] enforces authority rules
	↓
[Ouroboros] logs attempt (even if rejected)
	↓
Request proceeds OR fails securely
```

## Subdirectories

- **[Helios](./Helios/)** — Identity, consent, and session management
- **[Sirus](./Sirus/)** — Device context and request authority
- **[Ouroboros](./Ouroboros/)** — Signing, integrity, and audit trails

## Getting Started

### As a Consumer

```bash
composer require starisian/sparxstar-platform-contracts
```

Then import the IAMC interfaces:

```php
use SparxStar\Helios\Contracts\SPXHeliosClientInterface;
use SparxStar\Sirus\Contracts\SPXSirusClientInterface;
```

### Basic Pattern

1. On each request, validate identity with **Helios**
2. Capture device/network context with **Sirus**
3. Check authority rules with **Sirus**
4. Log critical operations with **Ouroboros**
5. Reject if any check fails (fail-closed)

## Key Concepts

### Fail-Closed
IAMC always **rejects by default**. If any validation can't complete or is uncertain, the request is rejected. Your app must then decide whether to retry, ask for more info, or fail the user request.

### Session Validity
Sessions are stateless but trustworthy. Helios generates tokens; every request must revalidate. Sessions can expire, be revoked, or become untrusted based on device context.

### Consent Layers
- **Technical consent** — "Can your API call our API?"
- **User consent** — "Did this user agree to this?"
- **Compliance consent** — "Does this comply with laws/regulations?"

All three must be satisfied.

### Device Context
Sirus tracks:
- Device fingerprint (OS, browser, hardware)
- Network conditions (latency, bandwidth, type)
- Geographic location (GeoIP, if available)
- Trust history (has this device been trustworthy?)

Context is captured but not stored; it's transient.

### Audit Immutability
Critical operations are signed by Ouroboros. Even if a hacker gets to your database:
- They can't forge the signature
- They can't delete the log
- You can prove who did what when

## Important Notes

### Versioning
IAMC follows semantic versioning. Breaking changes are rare and require governance approval.

### Network Latency
IAMC adds latency (typically 50-200ms per request). This is intentional — security requires verification. Cache where possible; don't call IAMC per millisecond.

### Storage Requirements
- Session tokens: Short-lived (Redis)
- Consent records: Long-lived (compliant storage)
- Audit logs: Permanent (append-only, signed)

### Compliance
IAMC is built for:
- **GDPR** — Consent tracking, right to deletion
- **CCPA** — User data requests, opt-out
- **SOC2** — Audit trails, access controls
- **HIPAA** — If healthcare-focused (additional config needed)

## Related Services

- **[DVE (Voice Engine)](../DVE/)** — Uses IAMC for audio request validation
- **[IAtlas (Data Structures)](../IAtlas/)** — Defines DTOs and schemas
- **[Starmus (Audio)](../Starmus/)** — Uses IAMC for audio service auth

## Support & Questions

- **Consent flows?** See [Helios documentation](./Helios/)
- **Device context?** See [Sirus documentation](./Sirus/)
- **Audit & signing?** See [Ouroboros documentation](./Ouroboros/)
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**
- **Security concern?** Email `security@starisian.tech`

---

**Security Foundation** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
