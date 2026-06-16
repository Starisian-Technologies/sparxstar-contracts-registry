# Dictionary — Shared Vocabulary

## What Is This?

Dictionary is the **shared vocabulary layer** for SPARXSTAR. It defines:

- **Enumerations** — Valid values for common concepts (languages, formats, error codes)
- **Constants** — System defaults (timeouts, limits, retry counts)
- **Validation rules** — What values are permitted
- **Error codes** — Standard errors across the platform
- **Terminology** — Shared meaning for key terms

Dictionary ensures that when one service says "language = en-US", every other service understands it the same way.

## Why You Need It

Without Dictionary:
- Services use different formats for the same concept (chaos)
- A typo in a string breaks silently
- Adding a new language requires updating 10 services

With Dictionary:
- All services speak the same language
- Invalid values are caught at compile time
- Adding new values is centralized
- Self-documenting code

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | Shared enums, constants, and terminology |
| **Use Case** | "What are the valid values for language?" |
| **Type Safety** | PHP enums ensure type safety |
| **Part Of** | IAtlas (Data Structures) |

## Common Enumerations

### Audio Formats
```php
AudioFormat::WAV
AudioFormat::MP3
AudioFormat::OGG
AudioFormat::FLAC
AudioFormat::M4A
```

### Languages (BCP-47)
```php
LanguageCode::EN_US      // English (US)
LanguageCode::EN_GB      // English (UK)
LanguageCode::ES_MX      // Spanish (Mexico)
LanguageCode::ES_ES      // Spanish (Spain)
LanguageCode::ZH_HANS    // Chinese (Simplified)
LanguageCode::ZH_HANT    // Chinese (Traditional)
```

### Device Types
```php
DeviceType::MOBILE
DeviceType::TABLET
DeviceType::DESKTOP
DeviceType::WEARABLE
DeviceType::UNKNOWN
```

### Job Status
```php
JobStatus::QUEUED
JobStatus::PROCESSING
JobStatus::COMPLETED
JobStatus::FAILED
JobStatus::CANCELLED
```

### Error Codes
```php
ErrorCode::INVALID_AUDIO_FORMAT
ErrorCode::UNSUPPORTED_LANGUAGE
ErrorCode::AUTHORIZATION_FAILED
ErrorCode::QUOTA_EXCEEDED
ErrorCode::NETWORK_TIMEOUT
```

## Getting Started

### Using Enums

```php
use SparxStar\\IAtlas\\Dictionary\\AudioFormat;
use SparxStar\\IAtlas\\Dictionary\\LanguageCode;

// Type-safe enum usage
$format = AudioFormat::MP3;           // ✓ Valid
$format = AudioFormat::from('mp3');   // ✓ Also valid
// $format = 'mp3';                    // ✗ Type error in strict mode

// Check valid values
foreach (AudioFormat::cases() as $fmt) {
	echo $fmt->value;  // 'wav', 'mp3', etc.
}
```

### Using Constants

```php
use SparxStar\\IAtlas\\Dictionary\\SystemConstants;

// Standard defaults across the platform
$timeout = SystemConstants::TRANSCRIPTION_TIMEOUT_SECONDS;      // 300
$maxRetries = SystemConstants::MAX_UPLOAD_RETRIES;              // 5
$maxJobQueueDepth = SystemConstants::MAX_CONCURRENT_JOBS;       // 5000
```

### Validation

```php
use SparxStar\\IAtlas\\Dictionary\\LanguageCode;

try {
	// Will throw ValueError if invalid
	$lang = LanguageCode::from('invalid');
} catch (ValueError $e) {
	// Only valid BCP-47 codes accepted
	log::error("Invalid language code: {$e->getMessage()}");
}
```

## Common Enumerations Reference

| Enum | Values | Purpose |
|------|--------|---------|
| `AudioFormat` | WAV, MP3, OGG, FLAC, M4A, OPUS | Audio file types |
| `LanguageCode` | en-US, es-MX, zh-Hans, etc. | BCP-47 language codes |
| `DeviceType` | MOBILE, TABLET, DESKTOP, WEARABLE | Device classification |
| `JobStatus` | QUEUED, PROCESSING, COMPLETED, FAILED | Async job states |
| `ErrorCode` | See section above | Platform errors |
| `ContentType` | AUDIO, TEXT, VIDEO, DOCUMENT | Content categories |
| `Priority` | LOW, NORMAL, HIGH, CRITICAL | Request priority |

## Key Concepts

### Enums vs. Strings

**Without Dictionary (strings):**
```php
function setLanguage($lang) {
	// No type checking; easy to typo
	if ($lang === 'en-US') { ... }  // What if called with 'en_US'?
}

setLanguage('en-US');   // ✓ works
setLanguage('en_US');   // ✗ silently fails
setLanguage('invalid'); // ✗ no error
```

**With Dictionary (enums):**
```php
function setLanguage(LanguageCode $lang) {
	// Type-safe; IDE knows all valid values
	match($lang) {
		LanguageCode::EN_US => '...',
		LanguageCode::ES_MX => '...',
	};
}

setLanguage(LanguageCode::EN_US);   // ✓ IDE auto-complete
setLanguage('en-US');               // ✗ Type error immediately
```

### Backed Enums
Dictionary uses PHP's "backed enums" — enums with associated values:
```php
enum AudioFormat: string {
	case WAV = 'wav';
	case MP3 = 'mp3';
	case OGG = 'ogg';
}

// Get the string value
echo AudioFormat::MP3->value;  // 'mp3'

// Convert from string
$fmt = AudioFormat::from('mp3');  // AudioFormat::MP3
```

### BCP-47 Language Codes
Format: `language[-script][-region][-variant]`

Examples:
- `en` — English (any region)
- `en-US` — English (United States)
- `zh-Hans` — Chinese (Simplified script)
- `pt-BR` — Portuguese (Brazil)

Dictionary uses full BCP-47 codes to be unambiguous.

## Common Integration Patterns

### Pattern 1: Type-Safe Configuration
```php
// Configuration with type safety
class AudioConfig {
	public function __construct(
		public AudioFormat $format = AudioFormat::MP3,
		public LanguageCode $language = LanguageCode::EN_US,
		public int $bitrate = 128000
	) {}
}

$config = new AudioConfig(
	format: AudioFormat::WAV,
	language: LanguageCode::ES_MX
);
```

### Pattern 2: Validation Before Sending
```php
// Validate before hitting the API
function transcribe(
	string $filePath,
	string $languageCode
): JobResult {
	// Convert and validate in one step
	$lang = LanguageCode::from($languageCode);
    
	// Now safe to send to transcription service
	return $esu->transcribe($filePath, $lang);
}
```

### Pattern 3: Error Handling
```php
// Use Dictionary error codes for consistency
try {
	$result = $esu->transcribe(...);
} catch (TranscriptionException $e) {
	match($e->getErrorCode()) {
		ErrorCode::INVALID_AUDIO_FORMAT => $this->handleBadFormat(),
		ErrorCode::UNSUPPORTED_LANGUAGE => $this->handleBadLanguage(),
		ErrorCode::QUOTA_EXCEEDED => $this->queueForLater(),
		default => $this->logUnknownError($e),
	};
}
```

## Important Notes

### Versioning
Dictionary follows semantic versioning:
- Minor versions add new enums (safe)
- Major versions remove enums (breaking)

### Adding New Values
To add a new audio format to Dictionary:
1. Submit PR to `sparxstar-platform-contracts`
2. Governance review (ensures all services can handle it)
3. Merge to `sparxstar-platform-contracts`
4. This repo syncs automatically
5. All services now know about the new value

### Case Sensitivity
Enum names are case-sensitive in PHP:
```php
LanguageCode::EN_US      // ✓ Correct
LanguageCode::en_us      // ✗ Fatal error
LanguageCode::EN_us      // ✗ Fatal error
```

## Related Services

- **[IAtlas (Parent)](../)** — Dictionary is one component
- All SPARXSTAR services use Dictionary enums

## Support & Questions

- **What values are allowed for field X?** Check Dictionary
- **Need to add a new enum?** Submit PR to **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**
- **Type safety questions?** Check PHP enum documentation

---

**Vocabulary Layer** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
