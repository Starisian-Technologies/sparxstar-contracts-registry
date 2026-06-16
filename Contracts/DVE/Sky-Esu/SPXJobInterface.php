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
 * Contract for a single async job.
 *
 * All jobs MUST define a retry policy (max 3) and a timeout.
 * Failed jobs that exhaust retries MUST be moved to the dead-letter queue.
 */
interface SPXJobInterface
{
    /**
     * Execute the job.
     *
     * @throws \RuntimeException On unrecoverable failure.
     */
    public function handle(): void;

    /**
     * Maximum number of retry attempts before the job is dead-lettered.
     * MUST return 3 per platform policy.
     */
    public function getMaxRetries(): int;

    /**
     * Timeout in seconds for a single job execution attempt.
     */
    public function getTimeoutSeconds(): int;

    /**
     * Unique job identifier for idempotency and tracking.
     */
    public function getJobId(): string;

    /**
     * Return the scalar descriptor for this job — used for JSON persistence.
     *
     * The descriptor MUST contain only JSON-serializable scalar values (or
     * nested arrays of scalars). Service objects and resources MUST NOT be
     * included. The queue's JobFactory will supply non-scalar dependencies
     * when reconstructing the job from this descriptor.
     *
     * @return array<string, mixed>
     */
    public function getDescriptor(): array;
}
