<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

use Starisian\Sparxstar\Sky\Dto\ConfidenceRecord;
use Starisian\Sparxstar\Sky\Dto\CorrectionInput;
use Starisian\Sparxstar\Sky\Dto\CorrectionResponse;
use Starisian\Sparxstar\Sky\Dto\RevisionNode;

/**
 * Contract for append-only correction and revision DAG storage.
 *
 * Platform invariant §6: no correction or revision is ever deleted.
 * Canonicality is computed from the full contribution graph at query time.
 *
 * Concrete implementation: {@see \Starisian\Sparxstar\Sky\Store\CorrectionStore}
 * Phase 5+: replace with Dheghom vault implementation.
 */
interface SPXCorrectionStoreInterface
{
    /**
     * Submit a correction and append the corresponding RevisionNode to the DAG.
     *
     * @param string           $transcriptId       Transcript artifact identifier.
     * @param string           $contributorId      Resolved contributor identity.
     * @param CorrectionInput  $input              Validated correction input.
     * @param ConfidenceRecord $currentConfidence  Confidence at time of submission.
     *
     * @throws \RuntimeException On storage failure.
     */
    public function submitCorrection(
        string $transcriptId,
        string $contributorId,
        CorrectionInput $input,
        ConfidenceRecord $currentConfidence,
    ): CorrectionResponse;

    /**
     * Return all RevisionNodes for the given transcript, ordered by timestamp ascending.
     *
     * @return RevisionNode[]
     */
    public function getRevisions(string $transcriptId): array;

    /**
     * Check whether any corrections have been submitted for the given transcript.
     */
    public function hasCorrections(string $transcriptId): bool;
}
