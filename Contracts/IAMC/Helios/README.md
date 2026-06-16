# SPARXSTAR Helios — Identity & Consent

## What Is This?

Helios is the **identity and consent engine** for the SPARXSTAR platform. It:

- **Verifies user identity** — Knows who is making requests
- **Manages consent** — Tracks what users have agreed to
- **Validates sessions** — Ensures active sessions are fresh and trusted
- **Enforces retention** — Deletes data according to policy
- **Provides audit trails** — Compliance-ready proof of consent

Every platform request authenticates through Helios before proceeding.

## Why You Need It

Consent is non-optional for modern applications. Helios ensures:

- **Legal compliance** — GDPR requires explicit, documented consent
- **User transparency** — Users know what you're doing with their data
- **Right to deletion** — Helios enforces retention and deletion policies
- **Audit readiness** — Prove to regulators that you have consent
- **Zero trust** — Require fresh consent validation for every critical operation

Without Helios, you can't legally operate. With it, you're compliant by design.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | Identity verification and consent management |
| **Input** | User session tokens, operation context |
| **Output** | Identity context, consent verification |
| **Consumers** | All platform services |
| **Part Of** | IAMC (Identity, Auth, Messaging & Context) |

## Core Capabilities

### Session Validation
- **Token verification** — Is this session real and valid?
- **Freshness checking** — Is the session still within TTL?
- **Revocation detection** — Has the user logged out elsewhere?
- **MFA enforcement** — Require step-up authentication if needed
- **Device binding** — Verify the device matches the original session

### Consent Management
- **Granular consent** — Track consent per operation/data-use
- **Version tracking** — Know which consent version users accepted
- **Scope enforcement** — Prevent scope creep in operations
- **Time-bound consent** — Require re-consent after time period
- **Withdrawal** — Honor requests to revoke consent

### Data Retention
- **Policy enforcement** — Delete data according to agreed schedules
- **Retention tracking** — Know when data should be deleted
- **Right to deletion** — Respond to user deletion requests
- **Audit compliance** — Keep logs even after data is deleted

## How It Works

```
User makes request with session token
	↓
[Helios] look up session in token store
	↓
[Helios] verify signature and TTL
	↓
[Helios] check if session revoked
	↓
[Helios] verify device context matches
	↓
[Helios] check consent for this operation
	↓
Return identity context OR reject
```

## Getting Started

### Basic Session Validation

```php
use SparxStar\\Helios\\Contracts\\SPXHeliosClientInterface;

$helios = $container->get(SPXHeliosClientInterface::class);

// On each request, validate session
$session = $helios->validateSession(
	token: $_SESSION['session_token'],
	request: $httpRequest,
	device: $deviceContext
);

if ($session->isValid()) {
	$userId = $session->getUserId();
	// Proceed with request
} else {
	// Reject: expired, revoked, or invalid
	http_response_code(401);
}
```

### Checking Consent

```php
// Before accessing user data
$hasConsent = $helios->verifyConsent(
	userId: $userId,
	operation: 'data.analytics',
	scope: 'user.behavior'
);

if (!$hasConsent) {
	// User hasn't consented to analytics
	// Either ask for consent or skip analytics
}
```

### Enforcing Data Deletion

```php
// User requests deletion
$helios->requestDeletion(
	userId: $userId,
	dataScope: 'all'  // or 'analytics', 'messages', etc.
);

// Helios schedules deletion per policy
// Your app must honor the deadline
```

## Key Concepts

### Sessions are Stateless
Helios doesn't store sessions on a server. Sessions are:
- **Encrypted tokens** — Contains user identity and metadata
- **Cryptographically signed** — Can't be forged
- **Short-lived** — Typically 15-60 minutes
- **Revocable** — Can be instantly invalidated

### Consent Tiers

**Technical Consent** ("Can API X call API Y?")
- Backend-to-backend authorization
- Controlled by service owners

**User Consent** ("Does the user agree?")
- Explicit opt-in for data use
- Tracked in Helios
- Can be withdrawn anytime

**Compliance Consent** ("Does this comply with law?")
- Regulatory requirements
- Geographic-specific (GDPR in EU, CCPA in CA, etc.)
- Enforced by Helios policy

### Retention vs. Deletion

**Retention**: How long data can be kept
- User data: Typically retained while account active
- Analytics: 1-2 years
- Logs: 7 years (compliance)

**Deletion**: When data must be removed
- On user request (right to deletion)
- On account closure
- After retention period expires

### Device Binding
Helios can bind sessions to specific devices:
- Session token works only on registered device
- If device changes unexpectedly, session invalidated
- Forces re-authentication from new device
- Prevents token theft from being useful

## Common Integration Patterns

### Pattern 1: Request Authentication
```
1. User logs in → Helios creates session token
2. Frontend stores token
3. On each request, send token in Authorization header
4. Backend calls helios->validateSession()
5. If valid, proceed; if not, return 401
```

### Pattern 2: Consent Workflow
```
1. User tries to access feature
2. Backend checks helios->verifyConsent()
3. If no consent, redirect to consent page
4. User reviews + agrees
5. helios->recordConsent()
6. User allowed to proceed
```

### Pattern 3: Right to Deletion
```
1. User in settings clicks "Delete my account"
2. Backend calls helios->requestDeletion()
3. User receives confirmation email
4. After 30-day grace period, Helios schedules deletion
5. Your app polls for deletion status
6. When scheduled, you delete all user data
7. Helios verifies deletion completed
```

## Important Notes

### Versioning
Helios follows semantic versioning. Breaking changes are rare and documented.

### Session TTL (Time-to-Live)
- Short-lived access tokens: 15 minutes
- Refresh tokens: 7 days (or longer)
- Always revalidate on each request
- Don't cache validation results for long periods

### Latency Impact
Helios adds typical latency of **50-150ms** per validation. This is intentional.

To optimize:
- Cache validation results briefly (30-60 seconds)
- Use connection pooling to Helios service
- Batch consent checks when possible

### Privacy By Design
- Helios doesn't store passwords
- Helios doesn't log request bodies
- Session tokens are encrypted in transit
- Audit logs are immutable and encrypted

### Compliance Readiness
Helios generates compliance reports for:
- **Consent audits** — Who consented to what
- **Deletion audits** — What was deleted and when
- **Access audits** — Who accessed whose data
- **Incident response** — Prove what happened during breach

## Related Services

- **[Sirus (Device Context)](../Sirus/)** — Provides device info to Helios
- **[Ouroboros (Signing)](../Ouroboros/)** — Signs audit logs
- **[IAMC (Parent)](../)** — Overall identity platform

## Support & Questions

- **How do I implement consent?** Start with the [Getting Started](#getting-started) section
- **GDPR compliance?** Email `compliance@starisian.tech`
- **Session management?** See interface docs in contract files
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**

---

**Identity & Consent Engine** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
