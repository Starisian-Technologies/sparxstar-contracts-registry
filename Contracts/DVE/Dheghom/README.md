# Dheghom

## What Is This?

Dheghom is a specialized DVE subsystem for **high-fidelity audio preprocessing and enhancement**. It optimizes raw audio before transcription, improving accuracy for noisy environments, multiple speakers, and low-bandwidth conditions.

## Why You Need It

Transcription accuracy depends heavily on audio quality. Dheghom ensures that even audio captured in challenging conditions—a busy café, multiple speakers, poor connectivity—gets normalized and enhanced before it reaches the transcription engine.

- **Noise Reduction** — Removes background noise intelligently
- **Audio Normalization** — Standardizes levels and formats
- **Echo Cancellation** — Clears up dual-speaker scenarios
- **Bandwidth Optimization** — Compresses without losing critical information
- **Quality Prediction** — Flags audio that may transcribe poorly

## Quick Overview

| Aspect | Detail |
|--------|--------|
| **Purpose** | Audio preprocessing and enhancement |
| **Input** | Raw audio files (any supported format) |
| **Output** | Optimized audio ready for transcription |
| **Called By** | Sky-Esu transcription pipeline |
| **Part Of** | DVE (Digital Voice Engine) |

## Key Capabilities

- Detect and remove background noise
- Normalize audio levels across files
- Cancel echo in two-way calls
- Compress intelligently for network efficiency
- Assess audio quality before processing
- Support multiple audio formats (WAV, MP3, OGG, FLAC)

## How It Works

1. **Audio Receipt** — Dheghom receives raw audio file
2. **Analysis** — Scans for noise, echo, normalization needs
3. **Processing** — Applies optimal preprocessing pipeline
4. **Quality Assessment** — Scores the result
5. **Output** — Returns enhanced audio to transcription queue

You typically don't call Dheghom directly; **Sky-Esu calls it automatically** as part of the transcription pipeline.

## Getting Started

Dheghom is internal to DVE. Reference it through the Sky-Esu interfaces:

```php
use SparxStar\Sky\Contract\SPXEsuInterface;
// Esu automatically routes through Dheghom internally
```

## Important Notes

- **Automatic** — Dheghom preprocessing is transparent; you don't configure it
- **Lossless Quality** — Preprocessing is tuned to preserve speech clarity
- **Supports all formats** — Automatically detects and converts audio formats
- **Real-time capable** — Can process even large files quickly

## Related Services

- **[Sky-Esu (Transcription)](../Sky-Esu/)** — Uses Dheghom internally
- **[Digital Voice Engine (DVE)](../)** — Parent service

---

**Part of SPARXSTAR DVE** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
