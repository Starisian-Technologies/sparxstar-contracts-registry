# SPARXSTAR Sky Esu

## What Is This?

Sky Esu is the **AI-powered transcription and translation engine** at the heart of DVE. It orchestrates automatic speech recognition (ASR) via Yahura, language processing via Behistun, and intelligent routing through Sirus authority validation—turning speech into accurate, translated text in real-time.

## Why You Need It

Esu is where voice becomes usable:

- **High-accuracy transcription** — Understands accents, technical terms, and context
- **Multi-language support** — BCP-47 language codes; automatic detection available
- **Instant translation** — Transcribe in one language, translate to another in one call
- **Async-first design** — Never blocks; always returns a job_id
- **Secure by default** — All requests validated through Sirus authority before processing
- **Correction learning** — Improves over time as users correct transcriptions

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | AI transcription and translation orchestration |
| **Input** | Audio file path (any format), language code, context |
| **Output** | Job ID (async), then transcription + optional translation |
| **Uses** | Yahura (ASR), Behistun (translation), Sirus (authority) |
| **Audience** | Speech-to-text applications, multilingual platforms |
| **Part Of** | DVE (Digital Voice Engine) |

## Core Capabilities

### Transcription
- **Automatic Speech Recognition** — Convert audio to text with high accuracy
- **Noise-robust** — Works in noisy environments (Dheghom preprocessing helps)
- **Speaker identification** — Can identify multiple speakers in one audio
- **Confidence scoring** — Know how confident the system is about each word
- **Custom vocabulary** — Learn domain-specific terms and proper nouns

### Translation
- **Single-call transcribe+translate** — Get both in one async job
- **50+ language pairs** — Major world languages supported
- **Cultural adaptation** — Translates idioms, not just words
- **Glossary support** — Preserve brand names and technical terms

### Reliability
- **TUS upload** — Resilient for low-bandwidth or unstable networks
- **Automatic retry** — Failed jobs retry with exponential backoff
- **Timeout handling** — Explicit failure if processing takes too long
- **Result caching** — Results stored durably; can re-fetch anytime

## How It Works

```
Audio File
	↓
[Esu] upload via TUS
	↓
[Esu] validate Sirus authority
	↓
[Esu] preprocess with Dheghom (if needed)
	↓
[Esu] dispatch to Yahura (ASR)
	↓
[Esu] optionally route to Behistun (translation)
	↓
[Esu] store result
	↓
Caller polls for completion
	↓
Return transcription + optional translation
```

## Getting Started

### Basic Transcription

```php
use SparxStar\Sky\Contract\SPXEsuInterface;

$esu = $container->get(SPXEsuInterface::class);

// Upload audio and request transcription
$jobId = $esu->transcribe(
	audioPath: '/tmp/user-voice-message.wav',
	languageBcp47: 'en-US',
	request: $httpRequest,
	caller: $currentUser
);

// Later, poll for result
do {
	sleep(2);
	$result = $jobFactory->getResult($jobId);
} while (!$result->isComplete());

echo $result->getTranscription();
// Output: "Hello, I'd like to report an issue with my account."
```

### Transcription + Translation

```php
$jobId = $esu->transcribeAndTranslate(
	audioPath: '/tmp/spanish-audio.wav',
	sourceLanguageBcp47: 'es-MX',
	targetLanguageBcp47: 'en-US',
	request: $httpRequest,
	caller: $currentUser
);

$result = $jobFactory->getResult($jobId);
// Output both versions
echo "Spanish: " . $result->getTranscription();
echo "English: " . $result->getTranslation();
```

## Key Concepts

### Job IDs
Every call returns a UUID v4. Store this to track and retrieve results. Job results are immutable and permanent.

### Async Pattern
Esu **never blocks**. It:
1. Returns a job_id immediately
2. Processes in the background
3. Stores the result durably
4. Returns the same result every time you ask for that job_id

### Sirus Authority
Before processing any audio, Esu validates:
- Is the caller authenticated? (via Helios identity)
- Does the caller have permission? (via Sirus)
- What device context applies? (network, device type)

If Sirus validation fails, the job is rejected immediately (fail-closed).

### Language Codes
Always use BCP-47 format (e.g., `en-US`, `es-MX`, `zh-Hans`). Invalid codes cause immediate validation errors.

### Correction Storage
When users correct a transcription, that correction is stored in the CorrectionStore. This data:
- Improves future transcription accuracy
- Is anonymized and aggregated
- Is subject to your data retention policies
- Can be deleted on user request

## Common Integration Patterns

### Pattern 1: Transcription in a Chat App
```
1. User presses 🎤, speaks message
2. Frontend uploads audio via TUS
3. Backend calls esu->transcribe()
4. Backend returns job_id to frontend
5. Frontend polls for result
6. When ready, display transcription as draft message
7. User can correct before sending
```

### Pattern 2: Customer Service Recording
```
1. Call ends
2. System uploads recording to backend
3. Backend calls esu->transcribeAndTranslate('call.wav', 'auto', 'en-US')
4. Transcription + English translation stored
5. Agent can review call summary
6. Translation accessible to English-speaking managers
```

### Pattern 3: Batch Processing for Archive
```
1. Company has 1000 archived audio files
2. Backend loops through and calls esu->transcribe() for each
3. Each gets a job_id
4. System polls all jobs in background
5. Results populated gradually over hours/days
6. Archive now searchable
```

## Important Notes

### Versioning
Sky Esu follows semantic versioning. Breaking changes are rare and always documented.

### Performance Characteristics
- **Typical latency**: 2–10 seconds for audio under 60 seconds
- **Concurrent jobs**: Handles thousands simultaneously
- **Languages**: 50+ supported natively
- **Accuracy**: 85–98% depending on audio quality and domain
- **Custom models**: Available for high-volume customers

### Cost Considerations
- Billing typically per-minute of audio processed
- Translation adds to cost (roughly 1.5x transcription cost)
- Bulk discounts available for high-volume use

### Network Resilience
- Audio upload uses TUS (resume-friendly if connection drops)
- Results cached permanently
- Job IDs survive service restarts
- Polling can happen from different servers

### Security & Privacy
- Audio files processed securely with encryption in transit
- Results cached but can be deleted on request
- Corrections are user data (subject to privacy laws)
- Sirus authority validation ensures only authorized access
- No audio content is logged; only metadata

### Error Handling
- **Invalid audio format** → Job fails with diagnostic message
- **Unsupported language** → Immediate validation error
- **Sirus failure** → Job rejected (fail-closed)
- **Network timeout** → Job fails after retry attempts
- **Quota exceeded** → Job queued; processes when capacity available

## Related Services

- **[Yahura (ASR Engine)](../../../IAMC/Sirus/)** — Underlying transcription engine
- **[Behistun (Translation)](../../../IAtlas/)** — Language translation
- **[Sirus (Authority & Context)](../../../IAMC/Sirus/)** — Authority validation
- **[Helios (Identity)](../../../IAMC/Helios/)** — User identity
- **[Dheghom (Preprocessing)](../Dheghom/)** — Audio enhancement
- **[Sky (Orchestration)](../Sky/)** — Parent routing layer
- **[DVE (Digital Voice Engine)](../)** — Parent service

## Support & Questions

- **How do I integrate?** Start with the [Getting Started](#getting-started) section above
- **Performance tuning?** Email `perf@starisian.tech`
- **Language not supported?** File an issue in **[sparxstar-dve](https://github.com/Starisian-Technologies/sparxstar-dve)**
- **Security concern?** Email `security@starisian.tech`
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**

---

**Core DVE Engine** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
