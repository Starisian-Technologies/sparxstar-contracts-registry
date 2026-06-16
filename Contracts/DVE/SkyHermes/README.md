# SkyHermes

## What Is This?

SkyHermes is DVE's **high-volume message and notification orchestrator**. It manages asynchronous messaging systems, voice notifications, SMS integration, and alert routing—ensuring your platform can communicate reliably at scale.

## Why You Need It

Modern applications need multi-channel communication:

- **Voice notifications** — Call users with alerts via voice synthesis or pre-recorded messages
- **Smart routing** — Send messages via the best channel (SMS, push, email, voice)
- **Retry logic** — Automatic retry with exponential backoff for failed deliveries
- **Rate limiting** — Prevent message floods; respect user preferences
- **Delivery tracking** — Know when messages are delivered, read, or failed

SkyHermes ensures communication reaches users reliably, even across unstable networks.

## Quick Overview

| Aspect | Detail |
|--------|--------|
| **Purpose** | Async messaging orchestration |
| **Channels** | Voice, SMS, push notifications, email |
| **Audience** | Alert systems, notification platforms |
| **Part Of** | DVE (Digital Voice Engine) |
| **Pattern** | Queue-based; fire-and-forget with tracking |

## Core Capabilities

- **Voice delivery** — Play audio notifications to users
- **Multi-channel routing** — Choose optimal channel per user
- **Retry management** — Auto-retry with exponential backoff
- **Rate limiting** — Respect user opt-ins and frequency caps
- **Delivery verification** — Confirm message received/read
- **Batch sending** — Efficient handling of 1000s of messages
- **User preferences** — Honor do-not-disturb, channel preferences
- **Logging & analytics** — Track all message attempts

## How It Works

```
Application sends message
	↓
[SkyHermes] validates recipient
	↓
[SkyHermes] checks user preferences + rate limits
	↓
[SkyHermes] selects best delivery channel
	↓
[SkyHermes] enqueues with retry strategy
	↓
[SkyHermes] delivers (may retry multiple times)
	↓
[SkyHermes] logs delivery status
	↓
Application polls for delivery confirmation
```

## Getting Started

SkyHermes is called from the Sky orchestration layer. Reference through the Sky interfaces:

```php
use SparxStar\Sky\Contract\SPXSkyHermesInterface;

$hermes = $container->get(SPXSkyHermesInterface::class);

// Send voice notification
$messageId = $hermes->sendVoiceNotification(
	userId: $user->getId(),
	message: 'Your package has been delivered.',
	priority: 'high',
	channels: ['voice', 'sms']
);

// Later, check delivery status
$status = $hermes->getDeliveryStatus($messageId);
```

## Key Concepts

### Message IDs
Every message gets a UUID v4. Track this to verify delivery and audit communication.

### Channels
- **Voice** — Synthesized or recorded audio to phone
- **SMS** — Text message to phone number
- **Push** — Native app notification
- **Email** — Email to address
- **In-app** — In-application notification badge

### Retry Strategy
Failed deliveries retry automatically:
- First retry: 1 minute
- Second retry: 5 minutes
- Third retry: 30 minutes
- Give up after 24 hours

### Rate Limiting
SkyHermes respects:
- User opt-in preferences
- Do-not-disturb schedules
- Maximum frequency (e.g., no more than 5 alerts per hour)
- Carrier limits (e.g., SMS rate limits)

## Common Patterns

### Pattern 1: Alert User of System Issue
```
1. System detects problem
2. Call hermes->sendVoiceNotification(userId, message)
3. System logs message_id
4. User receives voice call or SMS
5. Later, verify delivery via getDeliveryStatus()
```

### Pattern 2: Send to Many Users
```
1. Create notification for 50K users
2. Loop through users; call hermes->sendVoiceNotification() each
3. Get back 50K message_ids
4. Store all IDs for audit
5. System periodically checks delivery status
```

### Pattern 3: Smart Routing
```
1. Alert goes to user
2. SkyHermes checks user preferences
3. If voice preference = SMS, sends SMS instead
4. If user opted out of notifications, skips entirely
5. Respects do-not-disturb hours
```

## Important Notes

- **Async** — Calls return immediately; delivery happens later
- **No guarantees** — Messages may fail to deliver (network issues, opt-out, etc.)
- **Logging** — All message attempts logged for compliance
- **Compliance** — Respects TCPA (telemarketing), GDPR (email), other regulations
- **Cost** — Billing per message delivered (varies by channel)

## Related Services

- **[Sky (Orchestration)](../Sky/)** — Parent routing layer
- **[DVE (Digital Voice Engine)](../)** — Parent service

---

**Part of SPARXSTAR DVE** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
