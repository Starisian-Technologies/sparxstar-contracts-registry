<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

use Starisian\Sparxstar\Sky\Dto\TranscriptionResult;

/**
 * Contract for the Yahura containerised ASR (transcription) node.
 *
 * Sky Esu calls Yahura via this interface — it does not contain Yahura.
 */
interface SPXYahuraClientInterface
{
    /**
     * Submit audio for transcription.
     *
     * @param string $audioPath    Absolute path to the audio file (Opus or AAC-LC only at pipeline boundary).
     * @param string $languageBcp47 BCP-47 language code (e.g. "en-US", "yo-NG").
     *
     * @throws \RuntimeException On API failure after all retries are exhausted.
     */
    public function transcribe(string $audioPath, string $languageBcp47): TranscriptionResult;
}
