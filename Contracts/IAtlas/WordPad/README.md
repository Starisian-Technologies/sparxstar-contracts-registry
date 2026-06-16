# WordPad — Text Processing & NLP

## What Is This?

WordPad is the **text processing and natural language operations layer** for SPARXSTAR. It provides:

- **Language detection** — Identify which language text is in
- **Tokenization** — Break text into meaningful chunks
- **Text extraction** — Pull text from various formats
- **Format conversion** — Transform text between formats
- **Encoding handling** — Work with Unicode, special characters

WordPad abstracts the complexity of working with human language so services don't have to.

## Why You Need It

Text processing is surprisingly complex:
- Detecting language requires more than checking first few bytes
- Different scripts require different tokenization rules
- Unicode edge cases abound
- Performance matters at scale

WordPad handles it all, consistently.

## At a Glance

| Aspect | Detail |
|--------|--------|
| **Purpose** | Text processing and NLP operations |
| **Use Case** | Analyze, transform, or understand text |
| **Languages** | 50+ supported |
| **Part Of** | IAtlas (Data Structures) |

## Core Capabilities

### Language Detection
Identify what language text is in:
```php
$lang = $wordpad->detectLanguage("Bonjour, comment allez-vous?");
// Returns: LanguageCode::FR
```

Confidence scoring and mixed-language detection included.

### Tokenization
Break text into meaningful units (words, sentences, etc.):
```php
$tokens = $wordpad->tokenize(
	text: "The quick brown fox jumps.",
	language: LanguageCode::EN_US,
	level: TokenLevel::WORD
);
// Returns: ["The", "quick", "brown", "fox", "jumps", "."]
```

### Text Extraction
Pull text from various formats:
```php
$text = $wordpad->extractText(
	source: $pdfFile,
	format: DocumentFormat::PDF
);
// Returns: Plain text content
```

### Format Conversion
Convert text between formats:
```php
$html = $wordpad->convert(
	text: $markdown,
	from: TextFormat::MARKDOWN,
	to: TextFormat::HTML
);
```

### Encoding Handling
Work with various character encodings safely:
```php
$cleaned = $wordpad->normalize(
	text: $userInput,
	encoding: Encoding::UTF8,
	normalizationForm: NormalizationForm::NFC
);
```

## Getting Started

### Basic Language Detection

```php
use SparxStar\\IAtlas\\WordPad\\WordPadInterface;

$wordpad = $container->get(WordPadInterface::class);

// Simple detection
$detected = $wordpad->detectLanguage($text);
echo $detected->language;        // LanguageCode::ES_MX
echo $detected->confidence;      // 0.98 (98% confident)

// Specify limit for performance
$detected = $wordpad->detectLanguage(
	text: $text,
	maxCandidates: 3  // Return top 3 languages
);
```

### Tokenization

```php
// Word-level tokenization
$words = $wordpad->tokenize(
	text: "The quick brown fox.",
	language: LanguageCode::EN_US,
	level: TokenLevel::WORD
);
// ["The", "quick", "brown", "fox", "."]

// Sentence-level tokenization
$sentences = $wordpad->tokenize(
	text: "First sentence. Second sentence!",
	language: LanguageCode::EN_US,
	level: TokenLevel::SENTENCE
);
// ["First sentence.", "Second sentence!"]

// Include part-of-speech tags
$tagged = $wordpad->tokenizeWithPOS(
	text: "The cat sleeps.",
	language: LanguageCode::EN_US
);
// [
//   {word: "The", pos: "DET"},
//   {word: "cat", pos: "NOUN"},
//   {word: "sleeps", pos: "VERB"},
// ]
```

### Text Extraction from Documents

```php
// Extract from PDF
$text = $wordpad->extractText(
	source: '/tmp/document.pdf',
	format: DocumentFormat::PDF
);

// Extract from image with OCR
$text = $wordpad->extractText(
	source: '/tmp/screenshot.png',
	format: DocumentFormat::IMAGE,
	language: LanguageCode::EN_US  // Hint for OCR
);

// Extract with structure
$structured = $wordpad->extractStructured(
	source: '/tmp/invoice.pdf',
	format: DocumentFormat::PDF,
	template: 'invoice'  // Knows invoice layout
);
// Returns: {amount, date, vendor, items, ...}
```

### Format Conversion

```php
// Markdown to HTML
$html = $wordpad->convert(
	text: "# Title\\n## Subtitle",
	from: TextFormat::MARKDOWN,
	to: TextFormat::HTML
);

// Plain text to rich text
$rtf = $wordpad->convert(
	text: $plainText,
	from: TextFormat::PLAIN,
	to: TextFormat::RTF,
	options: ['font' => 'Courier New', 'size' => 12]
);
```

## Key Concepts

### Language Codes
WordPad uses BCP-47 language codes (from Dictionary):
- `en-US` — English (US)
- `es-MX` — Spanish (Mexico)
- `zh-Hans` — Chinese (Simplified)

### Token Levels

**WORD**
- Individual words
- Punctuation separate
- `"Hello, world!"` → `["Hello", ",", "world", "!"]`

**SENTENCE**
- Complete sentences
- Respects language-specific sentence boundaries
- Handles abbreviations correctly

**PARAGRAPH**
- Blocks of text separated by line breaks

**DOCUMENT**
- Entire document as single token

### Confidence Scoring
Language detection returns confidence:
- 0.0-1.0 scale
- `0.95` means 95% confident
- Low confidence (<0.8) may indicate:
  - Mixed languages
  - Short text
  - Unusual dialect

### Encoding Normalization
Ensures consistent text representation:
- `NFC` — Composed form (Apple, some databases prefer)
- `NFD` — Decomposed form (compatibility, some systems prefer)
- Both are "the same" text, but bytes differ

WordPad normalizes to avoid bugs from encoding surprises.

## Common Integration Patterns

### Pattern 1: Auto-Detect Language for Transcription
```php
$transcription = $result->getTranscription();

// Don't trust that audio language was correct
$detected = $wordpad->detectLanguage($transcription);

if ($detected->confidence > 0.9) {
	$detectedLang = $detected->language;
	// Can log if not what was expected
}
```

### Pattern 2: Extract Text from User Upload
```php
// User uploads a document
$text = $wordpad->extractText(
	source: $_FILES['document']['tmp_name'],
	format: DocumentFormat::from($mimeType)
);

// Detect language of content
$language = $wordpad->detectLanguage($text)->language;

// Store both
$document->setContent($text);
$document->setLanguage($language);
```

### Pattern 3: Tokenize for Search
```php
// User query: "The quick brown fox"
$tokens = $wordpad->tokenize(
	text: $_GET['q'],
	language: LanguageCode::EN_US,
	level: TokenLevel::WORD
);

// Search for all tokens
$results = $index->searchAll($tokens);
```

### Pattern 4: Normalize Before Storage
```php
// User input often has odd encoding
$normalized = $wordpad->normalize(
	text: $userInput,
	encoding: Encoding::UTF8,
	normalizationForm: NormalizationForm::NFC
);

// Store normalized version
$model->save($normalized);
```

## Important Notes

### Versioning
WordPad follows semantic versioning. Breaking changes are rare.

### Performance
- Language detection: <50ms per 1000 chars
- Tokenization: <10ms per 1000 words
- Text extraction: varies by document (1-30 seconds for PDFs)

Use caching for frequently analyzed text.

### Language Coverage
Supported languages include most major world languages (50+):
- European: English, Spanish, French, German, Italian, etc.
- Asian: Chinese (Simplified, Traditional), Japanese, Korean
- Middle Eastern: Arabic, Hebrew
- And many more

### Privacy
- Text is processed locally (not sent to cloud)
- No logging of text content
- Language detection doesn't require storing text

## Related Services

- **[IAtlas (Parent)](../)** — WordPad is one component
- **[Dictionary](../Dictionary/)** — Provides language codes and formats
- **[DVE (Voice Engine)](../../DVE/)** — Uses WordPad for transcript processing

## Support & Questions

- **Which languages are supported?** Check the [Language Coverage](#language-coverage) section
- **How accurate is language detection?** Typically 95%+ for text >50 characters
- **Performance concerns?** Email `ops@starisian.tech`
- **Architecture questions?** Issue in **[sparxstar-platform-contracts](https://github.com/Starisian-Technologies/sparxstar-platform-contracts)**

---

**Text & NLP Processing** | **Licensed:** GPL-2.0-or-later | **Updated:** 2026-06-16
