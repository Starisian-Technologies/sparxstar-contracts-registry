# SPARXSTAR IAtlas — Data Structures & Orchestration

## What Is This?

IAtlas is the **data model foundation** for the SPARXSTAR platform. It defines:

- **Dictionary** — Shared vocabulary and terminology
- **NodeEngine** — Workflow orchestration and DAG (Directed Acyclic Graph) processing
- **WordPad** — Text processing and natural language operations

IAtlas provides the DTOs, enums, and structures that every service uses to communicate. It's the platform's "Rosetta Stone" — making sure all services speak the same language.

## Why You Need It

Consistency matters. Without IAtlas:
- Services invent their own data structures (chaos)
- Translating between services is error-prone
- Adding new features requires touching 10 services

With IAtlas:
- All services share the same data models
- Adding a field happens in one place
- Services integrate easily
- New features scale cleanly

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Status** | Production-ready |
| **Audience** | Platform integrators, service developers |
| **Key Components** | Dictionary, NodeEngine, WordPad |
| **Dependencies** | None (platform-agnostic) |
| **Versioning** | Semantic; breaking changes require governance |

## Core Components

### Dictionary
Shared terminology and vocabulary:
- Enumerations (languages, device types, error codes)
- Constants (timeouts, limits, defaults)
- Validation rules (what values are valid?)
- Standard error codes

**Use when:** You need to know what values are allowed or what an enum means

### NodeEngine
Workflow orchestration and task processing:
- DAG (Directed Acyclic Graph) definitions
- Node types and constraints
- State machines
- Execution context and results

**Use when:** You're building multi-step workflows or complex processing

### WordPad
Text processing and NLP (Natural Language Processing) support:
- Text extraction
- Language detection
- Tokenization
- Format conversion

**Use when:** You're processing text, supporting multiple languages, or doing NLP

## How It Works

Most services reference IAtlas data models:

```
[DVE] needs to define audio job results
	↓
Uses IAtlas DTOs: JobResult, AudioMetadata
	↓
[Sky-Esu] returns result using same IAtlas structures
	↓
[Consumer] receives standardized data model
	↓
Knows exactly how to interpret the result
```

## Getting Started

### Using IAtlas in Your Code

```bash
composer require starisian/sparxstar-platform-contracts
```

nThen import the data models:

```php
use SparxStar\\IAtlas\\Dictionary\\AudioFormat;
use SparxStar\\IAtlas\\NodeEngine\\JobResult;
use SparxStar\\IAtlas\\WordPad\\LanguageCode;

// Use the shared enums and structures
$supportedFormats = AudioFormat::cases();  // [WAV, MP3, OGG, FLAC]

$result = new JobResult(
	jobId: 'uuid-v4-here',
	status: 'completed',
	transcription: 'Hello world'
);
```

## Subdirectories

- **[Dictionary](./Dictionary/)** — Shared enums, constants, and terminology
- **[NodeEngine](./NodeEngine/)** — Workflow orchestration and DAG processing
- **[WordPad](./WordPad/)** — Text processing and NLP support

## Key Concepts

### Single Source of Truth
Every service references IAtlas for common structures. This means:
- Change a data model → All services see the change
- No duplication → Less maintenance burden
- Type safety → Less runtime errors
- Self-documenting → The data model is the spec

### Backward Compatibility
IAtlas changes carefully:
- Adding fields is safe (old code ignores them)
- Removing fields requires major version bump
- Renaming requires deprecation period
- New enums added without breaking old code

### Validation
IAtlas structures often include validation:
```php
// Validation baked in
try {
	$lang = LanguageCode::from('invalid');
} catch (ValueError $e) {
	// Only valid BCP-47 codes accepted
}
```

### Type Safety
Using IAtlas structures provides PHP type hints:
```php
// IDE knows exactly what properties exist
function processJob(JobResult $result): string {
	return $result->transcription;  // IDE auto-completes
}
```

## Common Integration Patterns

### Pattern 1: Using Enums
```php
// Instead of hardcoding strings
// OLD: if ($format === 'wav') { ... }

// NEW: Use IAtlas enum
if ($format === AudioFormat::WAV) { ... }
```

### Pattern 2: Data Transfer Objects
```php
// Services communicate using IAtlas DTOs
$result = $esu->transcribe(...);

// Result is standardized IAtlas structure
echo $result->getTranscription();
echo $result->getLanguage();
echo $result->getConfidenceScore();
```

### Pattern 3: Workflow Definition
```php
// Define complex workflows using NodeEngine
$workflow = new Workflow(
	nodes: [
		new Node(name: 'upload', type: NodeType::IO),
		new Node(name: 'transcribe', type: NodeType::PROCESS),
		new Node(name: 'translate', type: NodeType::PROCESS),
	],
	edges: [
		['from' => 'upload', 'to' => 'transcribe'],
		['from' => 'transcribe', 'to' => 'translate'],
	]
);
```

## Important Notes

### Versioning
IAtlas follows semantic versioning. Breaking changes are rare and require governance approval.

### Performance
IAtlas structures are lightweight and fast:
- Enums are pre-compiled
- DTOs are plain PHP objects
- No database calls
- Typically <1ms to instantiate

### Extensibility
Services can extend IAtlas structures:
```php
// Safe to extend
class CustomJobResult extends JobResult {
	private $customField;
}
```

### Validation
Validate early using IAtlas structures:
```php
// IAtlas catches invalid values before they propagate
$format = AudioFormat::from($userInput);  // Throws if invalid
```

## Related Services

- **[DVE (Voice Engine)](../DVE/)** — Uses IAtlas for audio job structures
- **[IAMC (Identity)](../IAMC/)** — Uses IAtlas for consent and context DTOs
- **[Starmus (Audio)](../Starmus/)** — Uses IAtlas for audio service types

## Support & Questions

- **Which enum should I use?** See [Dictionary](./Dictionary/)
- **How do I define a workflow?** See [NodeEngine](./NodeEngine/)
- **Text processing?** See [WordPad](./WordPad/)
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**

---

**Data Foundation** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
