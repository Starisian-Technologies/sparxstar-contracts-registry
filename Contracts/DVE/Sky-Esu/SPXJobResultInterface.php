<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

/**
 * Contract for reading persisted async job results.
 *
 * Implementations expose the durable source of truth for async job lifecycle
 * state. The REST polling endpoint and transcript lookup depend on this
 * contract rather than on the queue implementation itself.
 *
 * Implementors:
 *   - JobResultReader — reads from the custom job results DB table (Phase 1)
 *   - Future: Redis/NATS cache layers on top of the DB source of truth
 */
interface SPXJobResultInterface
{
    /**
     * Retrieve the stored result for the given job ID.
     *
     * Returns null only when the job ID is unknown. Known jobs return their
     * current persisted status payload (pending/running/completed/failed).
     *
     * @return array<string, mixed>|null
     */
    public function getResult(string $jobId): ?array;

    /**
     * Check whether a result exists for the given job ID.
     *
     * True when the durable job result store contains a row for the job,
     * regardless of whether the job is pending, running, completed, or failed.
     */
    public function hasResult(string $jobId): bool;
}
