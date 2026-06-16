<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

use Starisian\Sparxstar\Sky\Upload\UploadStatus;

/**
 * Contract for TUS resumable file uploads.
 *
 * Platform rules (all enforced at this boundary):
 *   - Chunk size MUST NOT exceed 512 KB.
 *   - Every chunk MUST include a checksum.
 *   - Every upload MUST carry a UUID.
 *   - No full-file upload endpoint — chunked only.
 */
interface SPXTusUploadInterface
{
    /**
     * Initiate a new TUS upload and return the upload URL.
     *
     * @param string $uuid       Client-generated UUID v4 for this upload.
     * @param int    $totalBytes Total file size in bytes.
     * @param string $mimeType   MIME type (audio/ogg, audio/webm, audio/aac, audio/mpeg).
     *
     * @return string Upload URL for subsequent PATCH requests.
     */
    public function initiate(string $uuid, int $totalBytes, string $mimeType): string;

    /**
     * Receive a single upload chunk.
     *
     * @param string $uuid      Upload UUID.
     * @param string $checksum  SHA-256 hex checksum of this chunk's bytes.
     * @param int    $offset    Byte offset at which this chunk begins.
     * @param string $chunkData Raw binary chunk data (max 512 KB).
     *
     * @return int New byte offset after this chunk.
     *
     * @throws \InvalidArgumentException If chunk exceeds 512 KB or checksum fails.
     * @throws \RuntimeException         On storage failure.
     */
    public function receiveChunk(
        string $uuid,
        string $checksum,
        int $offset,
        string $chunkData,
    ): int;

    /**
     * Return the current upload status (offset, total, completion).
     */
    public function status(string $uuid): UploadStatus;

    /**
     * Resolve the absolute server filesystem path of a completed upload.
     *
     * The /transcribe REST endpoint accepts an upload UUID rather than a
     * client-supplied filesystem path, and the controller calls this method
     * to translate UUID → path immediately before dispatching the Yahura job.
     * This closes the trust-boundary gap where a logged-in client could
     * otherwise direct the ASR pipeline at an arbitrary server file path.
     *
     * Implementations MUST throw the typed exceptions below so callers can
     * map states to status codes without parsing exception messages.
     *
     * @throws \InvalidArgumentException             If the UUID format is invalid.
     * @throws \Starisian\Sparxstar\Sky\Upload\UploadNotFoundException     If no upload state row exists for this UUID.
     * @throws \Starisian\Sparxstar\Sky\Upload\UploadNotCompleteException  If the upload exists but is still in progress.
     * @throws \Starisian\Sparxstar\Sky\Upload\UploadDataMissingException  If the state says complete but the on-disk data is gone.
     */
    public function resolveCompletedFilePath(string $uuid): string;

    /**
     * Abort and clean up an in-progress upload.
     */
    public function abort(string $uuid): void;
}
