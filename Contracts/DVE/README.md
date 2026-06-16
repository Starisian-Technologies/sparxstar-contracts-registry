# SPARXSTAR Digital Voice Engine (DVE)

## What Is This?

DVE is the SPARXSTAR platform's **voice interaction and audio processing subsystem**. It handles everything from audio capture and transcription to voice-driven intelligence and real-time audio streaming—providing the infrastructure that makes voice a first-class interaction channel.

## Why You Need It

Modern applications need more than text: they need to understand voice, process audio reliably, and respond intelligently in real-time. DVE provides:

- **Accurate transcription** of spoken input with language support
- **Smart audio orchestration** that routes requests intelligently
- **Reliable upload mechanisms** for high-latency or unreliable networks
- **Background job processing** so voice interactions don't block users
- **Institutional-grade security** with context and authority validation

If your platform serves mobile users, relies on voice as input, or needs to process audio at scale, DVE is your foundation.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Status** | Production-ready |
| **Audience** | Platform integrators, voice-first apps |
| **Key Services** | Sky, Sky-Esu, SkyHermes, Dheghom, Mehns |
| **Dependencies** | Sirus (context), Helios (identity) |
| **Versioning** | Semantic; breaking changes rare |

## Core Functionality

DVE provides:

- **Audio Transcription** — Convert speech to text with high accuracy
- **Language Support** — Multi-language processing with BCP-47 codes
- **Async Job Processing** — Non-blocking voice requests with job tracking
- **Reliable Upload** — TUS-based uploads for resilient audio transfer
- **Correction Storage** — User-driven transcription corrections and learning
- **Intelligent Routing** — Context-aware dispatch via Sirus authority

## How It Works

Voice flows through DVE like this:

1. **Audio Capture** — User speaks; platform captures audio
2. **Upload** — Audio transferred via TUS (handles network issues gracefully)
3. **Sirus Authority Check** — DVE verifies the request is legitimate
4. **Job Dispatch** — Transcription job queued asynchronously (returns job_id immediately)
5. **Processing** — Transcription happens in the background
6. **Result Retrieval** — Caller polls or subscribes for the result
7. **Correction** — User can correct the transcription; DVE learns

See the service subdirectories for specific contract details:
- **Sky** — Core voice orchestration and routing
- **Sky-Esu** — AI-powered transcription and translation
- **SkyHermes** — High-volume message processing
- **Dheghom** & **Mehns** — Supporting services

## Getting Started

### As a Consumer

```bash
composer require starisian/sparxstar-platform-contracts
```

Then import the DVE interfaces:

```php
use SparxStar\Sky\Contract\SPXEsuInterface;
use SparxStar\Sky\Contract\SPXJobInterface;
use SparxStar\Sky\Contract\SPXTusUploadInterface;
```

### Basic Pattern

1. Authenticate request with **Helios**
2. Validate context with **Sirus**
3. Upload audio via **TUS** interface
4. Dispatch job via **Esu** interface
5. Poll **JobResult** for completion

### From Source

If you're maintaining the DVE source repository:

1. Update contracts in `sparxstar-dve`
2. Tag a release (e.g., `v1.2.0`)
3. This repo will automatically sync the changes
4. Document the amendment in your PR

## Key Concepts

### Job IDs
Every async operation returns a UUID v4 `job_id`. Store this to track progress, retrieve results, and cancel if needed.

### TUS Protocol
DVE uses the TUS (Tushy Upload Server) protocol for audio upload. It's designed for **unreliable networks** — if the connection drops, TUS resumes from where it stopped, not from the beginning.

### Language Codes
All language parameters use BCP-47 (e.g., `en-US`, `es-MX`, `zh-Hans`). Invalid codes trigger validation errors.

### Sirus Authority
All DVE operations require Sirus context. Requests without valid Sirus resolution **fail closed** — the system rejects them rather than guessing.

## Common Integration Patterns

### Pattern 1: Simple Transcription
1. User speaks into microphone
2. Browser sends audio to your backend via TUS
3. Your backend calls `Esu→transcribe()`
4. You poll `JobResult` until completion
5. Display transcription to user

### Pattern 2: Transcribe + Translate
1. User speaks in Spanish
2. Call `Esu→transcribeAndTranslate(audioPath, es-MX, en-US, ...)`
3. Get back job_id
4. Poll until complete
5. Show translated text

### Pattern 3: Batch Processing
1. Multiple users upload audio simultaneously
2. Each gets a job_id
3. DVE processes them concurrently in the background
4. Your app checks results as jobs complete

## Important Notes

### Versioning
DVE follows semantic versioning. **Breaking changes** (e.g., parameter renames, new required parameters) are rare and always documented. Minor updates add features without breaking compatibility.

### Network Awareness
DVE is **explicitly designed for 2G/3G networks and unreliable connections**. TUS upload never resumes from zero. Jobs are stored durably. Expect latency, but expect resilience.

### Storage of Corrections
When users correct transcriptions, those corrections are stored in the **CorrectionStore**. This data may be used for quality improvement. Check your jurisdiction's data retention laws.

### Security
- All requests must have valid **Helios identity**
- All requests must resolve valid **Sirus context**
- Audio files are processed securely; don't assume they're deleted immediately
- Corrections are considered user data and follow retention policies

## Related Services

- **[Helios (Identity)](../IAMC/Helios/)** — Provides user identity and consent context
- **[Sirus (Context & Authority)](../IAMC/Sirus/)** — Validates request authority and device context
- **[IAtlas (Data Structures)](../IAtlas/)** — Defines DTOs and data contracts

## Support & Questions

- Report bugs in the **[sparxstar-dve](https://github.com/Starisian-Technologies/sparxstar-dve)** repository
- Ask architecture questions in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)** issues
- Security concerns? Email `security@starisian.tech`

---

**Governed by:** Starisian Technologies | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16

