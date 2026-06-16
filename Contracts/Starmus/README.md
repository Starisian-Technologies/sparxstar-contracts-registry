# SPARXSTAR Starmus — Audio Service Contracts

## What Is This?

Starmus is the **audio service integration layer** for SPARXSTAR. It provides contracts and interfaces for:

- **Audio recording and capture** — Interface for microphone and audio input devices
- **Audio streaming** — Real-time audio transmission protocols
- **Audio processing** — DSP (Digital Signal Processing) operations
- **Audio storage** — Durably storing and retrieving audio files
- **Audio delivery** — Playback and audio output

Starmus is the "bridge" between audio devices and the rest of SPARXSTAR. It abstracts hardware complexity so higher-level services (like DVE) can focus on transcription and translation.

## Why You Need It

Audio hardware is messy:
- Different devices have different APIs
- Microphone permissions vary by OS
- Audio formats and codecs proliferate
- Real-time streaming requires careful buffer management

Starmus standardizes audio operations so you write once, work everywhere.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | Audio service contracts and abstractions |
| **Audience** | Audio-centric applications, voice platforms |
| **Key Abstractions** | Recording, streaming, processing, storage, playback |
| **Dependencies** | Helios (identity), Sirus (context), DVE (orchestration) |
| **Versioning** | Semantic; breaking changes rare |

## Core Capabilities

### Recording & Capture
- **Microphone access** — Request and manage microphone permissions
- **Audio capture** — Record from microphone or line-in
- **Format control** — Choose sample rate, bit depth, channels
- **Buffer management** — Efficient buffer handling for real-time recording
- **Device enumeration** — Discover available input devices

### Streaming
- **Real-time transmission** — Stream audio as it's captured
- **Adaptive bitrate** — Adjust quality based on network conditions
- **Jitter handling** — Handle network latency and packet loss
- **Buffering strategies** — Configure buffer sizes for different use cases
- **Protocol support** — WebRTC, custom protocols, RTP

### Processing
- **Audio effects** — Reverb, echo, noise gate, compressor
- **Filtering** — Low-pass, high-pass, band-pass filters
- **Equalization** — Tone shaping and frequency adjustment
- **Mixing** — Combine multiple audio sources
- **Gain control** — Volume adjustment and normalization

### Storage
- **File persistence** — Store audio to disk or cloud
- **Metadata handling** — Track duration, format, language, speaker
- **Retrieval** — Efficiently fetch stored audio
- **Lifecycle management** — Archive, delete, or migrate old audio
- **Encryption** — Optional encryption at rest

### Playback & Delivery
- **Speaker output** — Play audio through speakers or headphones
- **Volume control** — User-facing and system-level volume
- **Playback control** — Play, pause, seek, stop
- **Format support** — Transparently handle any audio format
- **Device routing** — Switch between speakers, headphones, Bluetooth

## How It Works

A typical audio workflow uses Starmus like this:

```
User enables microphone
	↓
[Starmus] requests permission (OS-specific)
	↓
[Starmus] opens audio input device
	↓
User speaks
	↓
[Starmus] captures audio in real-time
	↓
[Starmus] streams to backend (or buffers locally)
	↓
[DVE] receives audio and transcribes
	↓
[Starmus] plays back notification sound
	↓
Close microphone
```

## Getting Started

### Recording Audio

```php
use SparxStar\\Starmus\\Contracts\\SPXRecorderInterface;

$recorder = $container->get(SPXRecorderInterface::class);

// Check permissions and start recording
$session = $recorder->startRecording(
	deviceId: 'default-microphone',
	format: AudioFormat::WAV,
	sampleRate: 48000,
	bitDepth: 16,
	channels: 1  // Mono
);

// Recording happens in background
// Retrieve recorded data
sleep(5);  // User speaks for 5 seconds

$audioBuffer = $recorder->stopRecording($session->getId());
$audioData = $audioBuffer->getBytes();
```

### Streaming Audio

```php
use SparxStar\\Starmus\\Contracts\\SPXStreamingInterface;

$streamer = $container->get(SPXStreamingInterface::class);

// Start streaming recorded audio to server
$stream = $streamer->createStream(
	destination: 'wss://transcribe.starisian.tech/stream',
	format: AudioFormat::MP3,
	bitrate: 128000,
	adaptiveBitrate: true  // Auto-adjust for network conditions
);

$stream->write($audioBuffer);
$stream->close();
```

### Playback

```php
use SparxStar\\Starmus\\Contracts\\SPXPlaybackInterface;

$player = $container->get(SPXPlaybackInterface::class);

// Play a notification sound
$player->play(
	source: '/sounds/notification.mp3',
	volume: 0.8,  // 80% volume
	device: 'default-speaker'
);

// Block until done
$player->wait();
```

### Storage

```php
use SparxStar\\Starmus\\Contracts\\SPXAudioStorageInterface;

$storage = $container->get(SPXAudioStorageInterface::class);

// Store recorded audio
$metadata = $storage->store(
	audioData: $audioBuffer,
	metadata: [
		'userId' => $user->getId(),
		'format' => AudioFormat::WAV,
		'duration' => 5.3,
		'language' => LanguageCode::EN_US,
	]
);

$fileId = $metadata->getId();  // Use this to retrieve later

// Retrieve
$audioData = $storage->retrieve($fileId);
```

## Key Concepts

### Audio Formats
Starmus supports multiple formats:
- **WAV** — Uncompressed; large but high quality
- **MP3** — Compressed; widely compatible
- **OGG** — Compressed; open source
- **FLAC** — Lossless compression; high quality, smaller than WAV
- **OPUS** — Modern codec; optimal for speech

### Sample Rate
- **8 kHz** — Telephony quality
- **16 kHz** — Wideband (better for transcription)
- **44.1 kHz** — CD quality
- **48 kHz** — Professional audio

Use 16 kHz for voice; it's a good balance between quality and bandwidth.

### Real-Time Streaming
Starmus handles buffering transparently:
- If network is slow, buffer automatically expands
- If network catches up, buffer drains
- Users experience seamless audio even on 2G/3G

### Permission Handling
Different platforms require different permissions:
- **Web (browser)** — getUserMedia() prompt
- **iOS** — Info.plist NSMicrophoneUsageDescription
- **Android** — Manifest permission + runtime permission

Starmus abstracts these differences.

## Common Integration Patterns

### Pattern 1: Simple Voice Message
```
1. User clicks "Record"
2. Starmus starts recording
3. User speaks
4. Starmus stops on button release
5. Audio uploaded to backend
6. DVE transcribes
7. Result displayed
```

### Pattern 2: Live Transcription
```
1. User starts recording
2. Starmus streams audio to backend in real-time
3. DVE transcribes streaming audio
4. User sees transcription in real-time as they speak
5. User finishes speaking
```

### Pattern 3: Voice Notifications
```
1. Backend wants to notify user
2. Text-to-speech generates audio
3. Starmus plays audio through speakers
4. User hears notification
```

### Pattern 4: Audio Archive
```
1. All calls recorded to Starmus storage
2. Periodically transcribed via DVE
3. Old audio archived (lower cost storage)
4. Recent audio kept hot (fast access)
5. Compliance: retained per policy
```

## Important Notes

### Versioning
Starmus follows semantic versioning. Breaking changes are rare and documented.

### Privacy & Permissions
- **User consent required** — Always ask before recording
- **Transparent handling** — Tell users audio is being recorded
- **Helios integration** — Record consent using Helios contracts
- **Storage policy** — Enforce retention and deletion policies

### Performance
- Recording: <5ms latency to start
- Streaming: <100ms network latency (adaptive bitrate handles more)
- Playback: <100ms latency to start
- Storage: depends on backend (typically <1 second write)

### Security
- Audio encryption in transit (TLS/SSL)
- Audio encryption at rest (if configured)
- Device permissions honored (system-level sandboxing)
- Audit logging of all recording/playback

### Compliance
- **GDPR** — Respect right to deletion (Starmus enforces retention policies)
- **CCPA** — User can request audio be deleted
- **TCPA** — Consent tracking for outbound calls
- **HIPAA** — If healthcare-focused (encryption + audit logging)

## Related Services

- **[DVE (Voice Engine)](../DVE/)** — Uses Starmus for audio capture
- **[Helios (Identity)](../IAMC/Helios/)** — Manages consent for recording
- **[Sirus (Context)](../IAMC/Sirus/)** — Provides device context
- **[IAtlas (Data Structures)](../IAtlas/)** — Defines audio formats and constants

## Support & Questions

- **Microphone permissions?** Check browser console for errors; may be security policy
- **Audio quality issues?** Check sample rate and bit depth settings
- **Streaming latency?** Enable adaptive bitrate; check network conditions
- **Storage space?** Implement archival policy or increase storage capacity
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**

---

**Audio Integration Layer** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
