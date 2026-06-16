# SPARXSTAR Ouroboros — Signing & Integrity

## What Is This?

Ouroboros is the **integrity and audit engine** for SPARXSTAR. It:

- **Digitally signs data** — Proves data comes from who you think it does
- **Verifies integrity** — Detects if data has been tampered with
- **Creates audit trails** — Immutable records of what happened
- **Enables non-repudiation** — Signatories can't deny their actions
- **Supports compliance** — Forensics-ready audit logs

Ouroboros is the system's "proof machine" — it proves authenticity and creates indelible audit trails.

## Why You Need It

Trust requires proof. Ouroboros provides:

- **Tamper evidence** — Detect if data has been modified
- **Audit compliance** — Prove what happened and who did it
- **Non-repudiation** — User can't deny they signed something
- **Forensic ready** — Investigate incidents with certainty
- **Regulatory proof** — Show auditors you have integrity controls

In regulated industries (finance, healthcare), Ouroboros is mandatory.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | Digital signing, integrity, and audit trails |
| **Input** | Data to sign, key identifier, operation context |
| **Output** | Digital signature, audit log entry |
| **Consumers** | Compliance systems, critical operations |
| **Part Of** | IAMC (Identity, Auth, Messaging & Context) |

## Core Capabilities

### Digital Signing
- **Asymmetric signing** — Use private keys securely
- **Key rotation** — Roll keys without invalidating old signatures
- **Batch signing** — Efficiently sign many items
- **Hardware security module (HSM) support** — Enterprise-grade key storage
- **Time-stamping** — Prove when something was signed

### Integrity Verification
- **Signature verification** — Confirm data hasn't been tampered
- **Chain of custody** — Verify entire operation sequence
- **Checksum validation** — Detect corruption or transmission errors
- **Timestamp verification** — Prove operations happened in order

### Audit Trails
- **Immutable logs** — Append-only; can't be deleted or reordered
- **Signed entries** — Each log entry is signed by Ouroboros
- **Full context** — Who, what, when, where, why
- **Compliance formatting** — Meets regulatory audit standards
- **Long-term preservation** — Designed for 7+ year retention

## How It Works

```
Critical operation initiated
	↓
[Ouroboros] capture operation context
	↓
[Ouroboros] create audit log entry
	↓
[Ouroboros] sign the entry with private key
	↓
[Ouroboros] append to immutable log
	↓
[Ouroboros] optionally sign critical data
	↓
Audit trail created; operation proceeds
```

## Getting Started

### Signing Critical Data

```php
use SparxStar\\Ouroboros\\Contracts\\SPXOuroborosClientInterface;

$ouroboros = $container->get(SPXOuroborosClientInterface::class);

// Sign a critical agreement or transaction
$agreement = [
	'userId' => $user->getId(),
	'action' => 'data.deletion.request',
	'timestamp' => time(),
	'data' => json_encode(['scope' => 'analytics'])
];

$signature = $ouroboros->sign(
	payload: $agreement,
	keyId: 'ouroboros-prod-v1'
);

// Store both data and signature
$this->store->save($agreement, $signature);
```

### Verifying Signatures

```php
// Later, verify the data is unchanged
$isValid = $ouroboros->verify(
	payload: $storedAgreement,
	signature: $storedSignature,
	keyId: 'ouroboros-prod-v1'
);

if (!$isValid) {
	// Data has been tampered with!
	$this->alertSecurityTeam("Signature mismatch on agreement $agreementId");
	return false;
}
```

### Audit Logging

```php
// Log all critical operations
$ouroboros->logOperation(
	operation: 'payment.transfer',
	actor: $currentUser->getId(),
	target: $targetUser->getId(),
	amount: 1000.00,
	status: 'completed',
	details: ['method' => 'card', 'last4' => '4242']
);

// Ouroboros automatically signs the entry
// Entry appended to immutable log
```

### Retrieving Audit Trail

```php
// Retrieve audit entries for investigation
$auditTrail = $ouroboros->getAuditTrail(
	startDate: DateTime::createFromFormat('Y-m-d', '2026-06-01'),
	endDate: DateTime::createFromFormat('Y-m-d', '2026-06-16'),
	actor: $suspiciousUserId
);

foreach ($auditTrail as $entry) {
	echo "{$entry['actor']} did {$entry['operation']} at {$entry['timestamp']}\\n";
    
	// Verify each entry is signed correctly
	if (!$ouroboros->verifyAuditEntry($entry)) {
		echo "WARNING: Entry signature mismatch!\\n";
	}
}
```

## Key Concepts

### Digital Signatures

A digital signature is like a seal that proves:
1. **Who signed** — Only the holder of the private key could create it
2. **What was signed** — The signature only works for this exact data
3. **When** — Timestamp proves when signing happened
4. **Integrity** — If data changes, signature fails

**How it works:**
- Data is hashed (fingerprinted)
- Hash is encrypted with private key
- Anyone with public key can verify the signature
- But only the private key holder can create signatures

### Key Rotation

Old keys keep working; new operations use new keys:
- New signatures use `ouroboros-prod-v2` key
- Old signatures created with `ouroboros-prod-v1` still verify
- Keys can be scheduled for retirement
- Old keys kept in archive for verification only

### Immutable Logs

Audit logs are **append-only**:
- New entries always go to the end
- Existing entries never change
- Deletion impossible (by design)
- Reordering impossible (cryptographically verified)

If someone wants to cover up a log entry, they'd have to:
1. Delete or modify the entry
2. Resign all subsequent entries (but they don't have the private key)
3. The break in signatures would be obvious

### Non-Repudiation

If a user signs something with their key:
- They can't claim they didn't sign it
- The signature proves they did
- No plausible deniability

This makes Ouroboros critical for:
- Agreements and contracts
- Financial transactions
- Legal evidence
- Regulatory compliance

## Common Integration Patterns

### Pattern 1: Sign on Transaction
```
1. User initiates transfer of $10,000
2. Backend creates transaction record
3. Ouroboros signs the record
4. Transaction proceeds
5. Signature stored alongside transaction
```

### Pattern 2: Audit Compliance
```
1. Auditor requests proof of all access for past year
2. Backend calls ouroboros->getAuditTrail()
3. Ouroboros returns signed audit log
4. Auditor verifies each entry
5. Signatures prove logs are authentic
```

### Pattern 3: Incident Investigation
```
1. Security team detects suspicious activity
2. They retrieve audit trail for suspicious user
3. Each entry is cryptographically verified
4. If signatures break, that's where tampering occurred
5. Evidence is suitable for law enforcement
```

### Pattern 4: Regulatory Reporting
```
1. Regulator demands proof of data handling compliance
2. Company generates Ouroboros audit report
3. Report shows all data access, deletion, transfers
4. Each action is signed and time-stamped
5. Regulator accepts as authoritative proof
```

## Important Notes

### Versioning
Ouroboros follows semantic versioning. Breaking changes are rare and documented.

### Performance Impact
Signing is **fast** (typically <10ms) but not free:
- Cryptographic operations do have latency
- Don't sign on every single operation
- Sign critical/high-value operations
- Use batch signing for high volume

### Key Management
Private keys must be:
- Stored securely (hardware security module preferred)
- Never exported
- Rotated regularly
- Backed up securely
- Access-logged

### Audit Log Storage
Audit logs must be:
- Stored in tamper-evident storage
- Replicated to secure archive
- Retained per compliance policy (often 7 years)
- Encrypted at rest
- Accessed only via audit layer

### Compliance
- **SOC2** — Audit logs required; Ouroboros provides signed, immutable logs
- **HIPAA** — Non-repudiation and audit trail mandatory for healthcare
- **PCI-DSS** — Transaction signing and audit trails required
- **FINRA** — Financial industry requires non-repudiable records

## Related Services

- **[Helios (Identity)](../Helios/)** — Provides user identity; Ouroboros signs their actions
- **[Sirus (Authority)](../Sirus/)** — Authority decisions can be signed by Ouroboros
- **[IAMC (Parent)](../)** — Overall identity platform

## Support & Questions

- **How do I implement signing?** See [Getting Started](#getting-started) section
- **Key management?** Email `security@starisian.tech`
- **Audit compliance?** Email `compliance@starisian.tech`
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**
- **Incident investigation?** Email `forensics@starisian.tech`

---

**Signing & Integrity Engine** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
