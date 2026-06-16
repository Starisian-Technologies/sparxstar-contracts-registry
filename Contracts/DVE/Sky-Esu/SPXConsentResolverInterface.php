<?php
declare(strict_types=1);

/************************************************************************
 * STARISIAN TECHNOLOGIES - PROPRIETARY AND CONFIDENTIAL
 * Copyright © 2026 Starisian Technologies (Max Barrett)
 * PATENT PENDING — Inventors: Max Barrett (@MaximillianGroup), Obafa (@Obafa, select applications)
 * Jurisdiction: Los Angeles, CA
 ************************************************************************/

namespace Starisian\Sparxstar\Sky\Contract;

use SparxStar\Helios\Consent\SPXConsentReference;

/**
 * Resolves a consent reference into a concrete {@see SPXConsentReference} (and thus
 * a SPXRetentionClass) via Helios (POST /helios/v1/consent/resolve).
 *
 * FAIL-CLOSED CONTRACT: implementations MUST NOT throw. On any failure —
 * transport error, non-200, malformed body, missing/invalid fields, or an empty
 * consent reference — they MUST return {@see SPXConsentReference::ephemeral_null()},
 * never a vault grant. The carrier is retained only on a positive, well-formed
 * vault resolution; everything else is ephemeral by construction.
 *
 * The SPXConsentReference type is owned by the Helios contracts package
 * (SparxStar\Helios\Consent), mirrored locally until that package is resolvable
 * (see third-party/helios-contracts/README.md).
 */
interface SPXConsentResolverInterface
{
    /**
     * Resolve a consent reference id into its SPXConsentReference.
     *
     * @param string $consentId Opaque, Helios-owned consent reference id (from
     *                          the IngestManifest). An empty string resolves to
     *                          ephemeral_null() without a network call.
     *
     * @return SPXConsentReference Always returned; ephemeral_null() on any failure.
     */
    public function resolve(string $consentId): SPXConsentReference;
}
