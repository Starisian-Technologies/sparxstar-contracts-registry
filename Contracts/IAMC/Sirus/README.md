# SPARXSTAR Sirus — Device Context & Authority

## What Is This?

Sirus is the **context and authority engine** for the SPARXSTAR platform. It:

- **Captures device context** — Understands what device is making requests
- **Assesses request authority** — Ensures only authorized requests proceed
- **Detects anomalies** — Flags unusual or risky requests
- **Scores trustworthiness** — Assigns trust scores to requests
- **Enforces policies** — Applies rules based on context

If Helios says "who are you?" — Sirus says "where are you, what device are you on, and should I trust this request?"

## Why You Need It

Context is security. Sirus provides:

- **Anomaly protection** — Detect if a hacker got someone's password
- **Geographic awareness** — Flag impossible-to-reach locations
- **Device fingerprinting** — Know if the same device is being used
- **Network assessment** — Understand connection quality and stability
- **Behavioral analysis** — Spot patterns that indicate compromise

Modern fraud happens silently. Sirus gives you visibility.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | Device context and request authority |
| **Input** | HTTP request, device info, network metrics |
| **Output** | Device fingerprint, trust score, authority verdict |
| **Consumers** | All platform services |
| **Part Of** | IAMC (Identity, Auth, Messaging & Context) |

## Core Capabilities

### Device Fingerprinting
- **Hardware characteristics** — CPU, GPU, screen resolution
- **Browser/OS fingerprint** — OS version, browser type, plugins
- **Network characteristics** — ISP, subnet, connection type
- **Behavioral patterns** — Typing speed, mouse movement, app usage
- **History tracking** — "Have we seen this device before?"

### Authority Enforcement
- **Location-based rules** — Allow/deny based on geographic location
- **Time-based rules** — Flag requests outside normal hours
- **Risk rules** — Escalate for high-risk behaviors
- **Custom policies** — Define your own authority rules

### Trust Scoring
- **0-100 score** — 0 = untrusted, 100 = highly trusted
- **Multi-factor input** — Device history, location, behavior, time
- **Continuous assessment** — Score updated as request progresses
- **Adaptive thresholds** — Different thresholds for different operations

### Anomaly Detection
- **Impossible travel** — Request from NYC then Tokyo in 30 minutes
- **New device** — Device never seen from this user before
- **VPN/Proxy** — Detect attempts to hide origin
- **Velocity abuse** — Too many requests in short time window
- **Behavior deviation** — Patterns different from user baseline

## How It Works

```
Request arrives
	↓
[Sirus] capture device fingerprint
	↓
[Sirus] measure network characteristics
	↓
[Sirus] look up device history
	↓
[Sirus] check for anomalies
	↓
[Sirus] compute trust score
	↓
[Sirus] apply authority policies
	↓
Return verdict: ALLOW, CHALLENGE, or DENY
```

## Getting Started

### Basic Device Validation

```php
use SparxStar\\Sirus\\Contracts\\SPXSirusClientInterface;

$sirus = $container->get(SPXSirusClientInterface::class);

// Validate device context for this request
$context = $sirus->resolveContext(
	request: $httpRequest,
	userId: $userId
);

if ($context->isTrusted()) {
	// Device is known and trusted; proceed
} elseif ($context->isChallenge()) {
	// Device is new or suspicious; require step-up auth
	return $this->requireMfa();
} else {
	// Device is untrusted; reject
	http_response_code(403);
}
```

### Authority Check

```php
// Before sensitive operation
$authority = $sirus->validateAuthority(
	userId: $userId,
	operation: 'account.settings.change',
	request: $httpRequest
);

if (!$authority->isAuthorized()) {
	// Operation not authorized in this context
	throw new UnauthorizedException(
		"Operation requires challenge: " . $authority->getReason()
	);
}
```

### Custom Policy Rules

```php
// Define custom rules
$policy = $sirus->getPolicy($userId);

$policy->addRule('location', [
	'allowed' => ['US', 'CA', 'MX'],
	'action' => 'CHALLENGE'  // Ask for MFA if outside
]);

$policy->addRule('time', [
	'allowed_hours' => '08:00-18:00',
	'allowed_days' => 'weekdays',
	'action' => 'DENY'
]);

$sirus->setPolicy($userId, $policy);
```

## Key Concepts

### Trust Scores

**Factors that increase trust (0-100):**
- Device seen many times before (+30)
- Within known geographic region (+20)
- During normal hours (+15)
- Consistent behavioral pattern (+20)
- Stable network (+10)

**Factors that decrease trust:**
- New device (-50)
- Geographic impossibility (-100)
- VPN/Proxy detected (-30)
- Behavior anomaly (-20)
- Multiple failed auth attempts (-15)

**Using the score:**
- 80-100: Allow without challenge
- 50-79: Require step-up auth (MFA, verification question)
- 0-49: Require strong auth (MFA + device verification)
- Negative: Deny outright

### Context Layers

**Network Context**
- ISP and ASN (Autonomous System Number)
- Geographic location (GeoIP)
- Connection stability
- Proxy/VPN detection

**Device Context**
- OS and version
- Browser fingerprint
- Hardware characteristics
- Installed apps (if mobile)

**Behavioral Context**
- Request timing patterns
- Request volume patterns
- Device combinations (does user switch between devices?)
- Geographic movement patterns

### Fail-Closed Philosophy

Sirus defaults to **caution**:
- New device? Challenge.
- Impossible travel? Deny.
- Unknown context? Challenge.

It's better to inconvenience a legitimate user with MFA than to let a hacker proceed.

## Common Integration Patterns

### Pattern 1: Transparent Security
```
1. User logs in from laptop
2. Device recognized and trusted
3. Sirus allows access without friction
4. User doesn't notice anything
```

### Pattern 2: MFA Challenge on New Device
```
1. User logs in from new phone
2. Sirus detects new device
3. System requires step-up auth (MFA)
4. User completes MFA
5. Phone registered as trusted device
6. Future requests from phone proceed smoothly
```

### Pattern 3: Geographic Anomaly
```
1. User in NYC makes request
2. 30 minutes later, request from Tokyo
3. Sirus flags as impossible travel
4. System blocks request
5. User calls support to verify
6. Support unblocks manually
```

### Pattern 4: Adaptive Risk Rules
```
1. High-value operation (money transfer)
2. Sirus checks trust score
3. Score is 65 (medium)
4. System requires additional verification (security questions)
5. User answers; proceeding
```

## Important Notes

### Versioning
Sirus follows semantic versioning. Breaking changes are rare and documented.

### Performance Impact
Sirus adds typical latency of **50-200ms** depending on:
- Device history lookup
- Anomaly detection complexity
- Network context resolution

Optimize by:
- Caching device fingerprints (1 hour)
- Batching context queries
- Using async anomaly detection for non-critical paths

### Privacy Considerations
- Device fingerprints are hashed; original data not stored
- Behavioral patterns are aggregated; not individual actions
- Location data is generalized (city-level, not street-level)
- User can opt out of certain tracking (affects trust score)

### Compliance
- **GDPR**: Transparent about what data is collected
- **CCPA**: User can request device fingerprint deletion
- **SOC2**: Audit logs of all Sirus decisions
- **PCI-DSS**: Fraud detection supports compliance

## Related Services

- **[Helios (Identity)](../Helios/)** — Provides user identity; Sirus adds context
- **[Ouroboros (Signing)](../Ouroboros/)** — Signs authority decisions
- **[IAMC (Parent)](../)** — Overall identity platform
- **[DVE (Voice Engine)](../../DVE/)** — Uses Sirus to validate voice requests

## Support & Questions

- **How do I set authority rules?** See [Custom Policy Rules](#custom-policy-rules) above
- **Trust score too strict?** Email `support@starisian.tech` to adjust thresholds
- **Debugging anomaly detection?** Check audit logs or email `ops@starisian.tech`
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**

---

**Device Context & Authority Engine** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
