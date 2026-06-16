<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

use Starisian\Sparxstar\Sky\Dto\TranslationResult;

/**
 * Contract for the Behistun containerised translation/orthography node.
 *
 * Behistun uses HuggingFace NLLB for cloud and Llama-3-8B-Instruct for
 * sovereign vault deployments. Sky Esu calls Behistun via this interface.
 */
interface SPXBehistunClientInterface
{
    /**
     * Translate text from one language to another.
     *
     * @param string $text        UTF-8 source text.
     * @param string $sourceBcp47 BCP-47 source language code.
     * @param string $targetBcp47 BCP-47 target language code.
     *
     * @throws \RuntimeException On API failure after all retries are exhausted.
     */
    public function translate(
        string $text,
        string $sourceBcp47,
        string $targetBcp47,
    ): TranslationResult;

    /**
     * Apply orthographic normalisation to text in the given language.
     *
     * @param string $text        UTF-8 input text.
     * @param string $languageBcp47 BCP-47 language code.
     *
     * @throws \RuntimeException On API failure after all retries are exhausted.
     */
    public function applyOrthography(string $text, string $languageBcp47): string;
}
