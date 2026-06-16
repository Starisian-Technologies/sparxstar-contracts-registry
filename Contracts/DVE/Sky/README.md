# Sky

## What Is This?

Sky is the **orchestration layer** for DVE (Digital Voice Engine). It routes audio requests intelligently, manages job queues, and coordinates between transcription engines, preprocessing, and error handling—ensuring voice requests flow reliably from capture to completion.

## Why You Need It

Voice pipelines are complex. Sky abstracts that complexity:

- **Intelligent Routing** — Directs requests to the right processor based on language, priority, and load
- **Job Management** — Tracks async work from start to finish
- **Queue Coordination** — Prevents bottlenecks; prioritizes high-value requests
- **Resilience** — Retries failed jobs, handles timeouts gracefully
- **Monitoring** — Visibility into every job's state and performance

If DVE is the engine, Sky is the transmission.

## Quick Overview

| Aspect | Detail |
|--------|--------|
| **Purpose** | Voice request orchestration and routing |
| **Consumers** | Applications calling DVE transcription |
| **Coordinates** | Sky-Esu, Sirus, Helios, job storage |
| **Pattern** | Async: request → job_id → poll result |
| **Part Of** | DVE (Digital Voice Engine) |

## Core Functionality

- **Request Validation** — Checks auth, consent, and authority
- **Smart Routing** — Routes to transcription engine based on language and load
- **Job Lifecycle** — Tracks jobs from queued → processing → complete → archived
- **Priority Handling** — Premium users or critical requests get priority
- **Error Recovery** — Automatic retry with backoff
- **Timeout Management** — Prevents hung jobs; surfaces failures clearly

## How It Works

```
User speaks
    ↓
[Sky] validates request + checks authority
    ↓
[Sky] routes to Esu or Dheghom as needed
    ↓
[Sky] enqueues job, returns job_id
    ↓
[Sky] tracks progress in background
    ↓
[Sky] stores result when complete
    ↓
Caller polls for result
```

## Getting Started

You interact with Sky through the **Esu interface** (which uses Sky internally):

```php
use SparxStar\Sky\Contract\SPXEsuInterface;

$esu = $container->get(SPXEsuInterface::class);

// Request transcription
$jobId = $esu->transcribe(
    audioPath: '/tmp/audio.wav',
    languageBcp47: 'en-US',
    request: $request,
    caller: $caller
);

// Later, poll for result
$result = $jobFactory->getResult($jobId);
if ($result->isComplete()) {
    echo $result->getTranscription();
}
```

## Key Concepts

### Job IDs
Every transcription returns a UUID v4. Store this to retrieve results later. Job IDs are unique and immutable.

### Async Pattern
Sky operates **asynchronously** — calls return immediately with a job_id. The actual work happens in the background. This ensures:
- Your app never blocks waiting for transcription
- Multiple requests process in parallel
- Network drops don't lose work

### Authority & Context
Sky requires that **Sirus validates the request** before dispatching. If Sirus fails, the job is rejected (fail-closed). This ensures:
- Only authorized users transcribe
- Device context is verified
- Network conditions are understood

### Priority & Load Balancing
Sky monitors queue depth and routes intelligently:
- Short audio files may be routed to fast engines
- Premium accounts may get queue priority
- High load may trigger graceful degradation
- Peak hours use distributed processing

## Common Patterns

### Pattern 1: Simple Async Transcription
```
1. User speaks
2. POST audio to backend → get job_id
3. Return job_id to client
4. Client polls for result
5. Display transcription when ready
```

### Pattern 2: Transcription with Translation
```
1. User speaks Spanish
2. Sky routes to Esu with lang=es-MX, targetLang=en-US
3. Get job_id
4. Poll until complete
5. Result contains both transcription and translation
```

### Pattern 3: Batch Processing
```
1. Multiple users upload audio
2. Sky enqueues all jobs
3. Processes concurrently in background
4. Each user polls their own job_id
5. Results available as soon as ready
```

## Important Notes

### Versioning
Sky follows semantic versioning. Minor updates add features; breaking changes are rare and documented.

### Error Handling
- **Sirus failures** → Job rejected immediately (fail-closed)
- **Invalid audio** → Job fails with clear error message
- **Queue overflow** → Jobs queue up; oldest/highest-priority go first
- **Processing timeouts** → Job fails with diagnostic information

### Performance
- Average transcription latency: 2-10 seconds depending on audio length
- Handles 1000s of concurrent jobs
- TUS upload adapts to network conditions
- Queue processing is continuous (24/7)

### Network Resilience
Sky is **designed for unreliable networks**:
- Audio upload uses TUS (resume-friendly)
- Results cached durably
- Job tracking survives service restarts
- No single point of failure for critical paths

## Related Services

- **[Sky-Esu (Transcription)](../Sky-Esu/)** — AI engine Sky routes to
- **[Sirus (Authority & Context)](../../IAMC/Sirus/)** — Validates requests
- **[Helios (Identity)](../../IAMC/Helios/)** — Provides user identity
- **[Digital Voice Engine (DVE)](../)** — Parent service

## Support & Questions

- Architecture questions? Open an issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**
- Implementation bugs? Report in **[sparxstar-dve](https://github.com/Starisian-Technologies/sparxstar-dve)**
- Performance concerns? Email `ops@starisian.tech`

---

**Core DVE Service** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
