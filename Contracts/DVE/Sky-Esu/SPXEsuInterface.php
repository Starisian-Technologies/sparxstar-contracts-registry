<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

use Starisian\Sparxstar\Sky\Exception\SirusResolutionException;

/**
 * Contract for the Èṣù interaction AI orchestrator.
 *
 * Esu is the interaction AI built PHP-internal inside Sky Esu. It orchestrates
 * Yahura (ASR) and Behistun (translation/orthography) behind the Sirus authority
 * layer. All production requests are dispatched asynchronously — methods return
 * a job_id (UUID v4) immediately.
 *
 * Concrete implementation: {@see \Starisian\Sparxstar\Sky\Ai\Esu}
 */
interface SPXEsuInterface
{
    /**
     * Enqueue an async transcription job and return its job_id.
     *
     * Sirus context and authority MUST resolve before the job is dispatched.
     *
     * @param string $audioPath     Absolute path to audio file.
     * @param string $languageBcp47 BCP-47 language code.
     * @param mixed  $request       Raw request object forwarded to Sirus.
     * @param mixed  $caller        Caller identity forwarded to Sirus.
     *
     * @return string UUID v4 job_id.
     *
     * @throws SirusResolutionException On Sirus failure — caller must fail closed.
     * @throws \RuntimeException        On job dispatch or queue failure.
     */
    public function transcribe(
        string $audioPath,
        string $languageBcp47,
        mixed $request,
        mixed $caller,
    ): string;

    /**
     * Enqueue an async transcribe-then-translate job and return its job_id.
     *
     * @param string $audioPath   Absolute path to audio file.
     * @param string $sourceBcp47 BCP-47 source language code.
     * @param string $targetBcp47 BCP-47 target language code.
     * @param mixed  $request     Raw request object forwarded to Sirus.
     * @param mixed  $caller      Caller identity forwarded to Sirus.
     *
     * @return string UUID v4 job_id.
     *
     * @throws SirusResolutionException On Sirus failure.
     */
    public function transcribeAndTranslate(
        string $audioPath,
        string $sourceBcp47,
        string $targetBcp47,
        mixed $request,
        mixed $caller,
    ): string;
}
