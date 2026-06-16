<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

use Starisian\Sparxstar\Sky\Dto\Dictionary\DictionaryEntry;
use Starisian\Sparxstar\Sky\Dto\Dictionary\DomainTerm;
use Starisian\Sparxstar\Sky\Dto\Dictionary\LanguageTerm;
use Starisian\Sparxstar\Sky\Dto\Dictionary\SearchItem;
use Starisian\Sparxstar\Sky\Dto\Dictionary\SpellResult;
use Starisian\Sparxstar\Sky\Dto\Dictionary\WordlistEntry;

/**
 * Read-only contract for the external 3iAtlas Dictionary API (the official,
 * "blessed" lexicon — spelling, definitions, translations).
 *
 * Sky Esu is a pure CONSUMER of this service: it authenticates with a consumer
 * API key and only reads. The official list is never edited from this side —
 * the contract intentionally exposes no mutation. The intake pipeline consults
 * it to know, from go, whether a transcribed word is a real word and to anchor
 * the spelling/translation/meaning confidence dimensions when it is.
 *
 * Implementations throw {@see \Starisian\Sparxstar\Sky\Exception\DictionaryApiException}
 * on API error envelopes and transport failures, so callers fail closed.
 */
interface SPXDictionaryClientInterface
{
    /**
     * Resolve a single official entry by its slug.
     *
     * @param string $slug         The entry slug (not the raw word; use search() to resolve a word to a slug).
     * @param bool   $includeAudio When true, requests the entry's audio_url.
     *
     * @return DictionaryEntry|null Null only when the API reports a genuine miss —
     *   error code 'not_found' with HTTP 404. Every other failure surfaces.
     *
     * @throws \Starisian\Sparxstar\Sky\Exception\DictionaryApiException On any error
     *   other than a 'not_found'/404 miss — including a malformed request
     *   ('missing_param'/400), REST infrastructure errors ('rest_*'), rate limits,
     *   and transport failures. Callers fail closed.
     */
    public function lookup(string $slug, bool $includeAudio = false): ?DictionaryEntry;

    /**
     * Full-text search for official entries matching a query.
     *
     * @param string      $query      The search query.
     * @param string|null $langSource Optional BCP-47/source-language filter.
     * @param int|null    $perPage    Optional page size.
     *
     * @return array<int, SearchItem>
     *
     * @throws \Starisian\Sparxstar\Sky\Exception\DictionaryApiException On API error or transport failure.
     */
    public function search(string $query, ?string $langSource = null, ?int $perPage = null): array;

    /**
     * Validate words against the official lexicon, with nearest-headword suggestions.
     *
     * @param array<int, string> $words One or more words to check.
     *
     * @return array<int, SpellResult>
     *
     * @throws \Starisian\Sparxstar\Sky\Exception\DictionaryApiException On API error or transport failure.
     */
    public function spell(array $words): array;

    /**
     * Bulk-list official headwords for a language (Consumer API key only).
     *
     * @param string   $langSource   The source-language filter (required).
     * @param int|null $perPage      Optional page size.
     * @param int|null $page         Optional 1-based page number.
     * @param bool     $includeAudio When true, sends include_audio=true so each row
     *   carries WordlistEntry::$audioUrl (a direct media URL, or null when no audio
     *   is recorded). When false the audio_url field is absent and $audioUrl is null.
     *
     * @return array<int, WordlistEntry>
     *
     * @throws \Starisian\Sparxstar\Sky\Exception\DictionaryApiException On API error or transport failure.
     */
    public function wordlist(string $langSource, ?int $perPage = null, ?int $page = null, bool $includeAudio = false): array;

    /**
     * List the languages the official dictionary covers.
     *
     * @return array<int, LanguageTerm>
     *
     * @throws \Starisian\Sparxstar\Sky\Exception\DictionaryApiException On API error or transport failure.
     */
    public function languages(): array;

    /**
     * List the subject domains the official dictionary covers.
     *
     * @return array<int, DomainTerm>
     *
     * @throws \Starisian\Sparxstar\Sky\Exception\DictionaryApiException On API error or transport failure.
     */
    public function domains(): array;
}
