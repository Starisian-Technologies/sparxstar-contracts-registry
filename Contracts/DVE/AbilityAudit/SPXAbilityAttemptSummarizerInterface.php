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
 * Reduces raw ability input to a shape description that carries no value.
 *
 * This is the security boundary of the whole audit path. `wp_ability_invoked`
 * fires before the permission check, so its input is untrusted and
 * attacker-controlled: it may hold credentials, tokens, personal data, or a
 * payload sized to exhaust the logger. The summary is the ONLY representation
 * of that input permitted to leave memory.
 *
 * Implementations MUST pass the adversarial vectors in redaction-vectors.json
 * unmodified. Those vectors are the conformance test, not an example set.
 */
interface SPXAbilityAttemptSummarizerInterface
{
    /**
     * @param mixed $input Raw, unvalidated ability input.
     * @return array{
     *     type: string,
     *     bytes: int,
     *     digest: string,
     *     shape: mixed,
     *     redaction: string
     * }
     *   type      — the input's PHP type, nothing more.
     *   bytes     — byte length of the input's JSON encoding. Size is the
     *               signal that distinguishes a normal call from an attempt to
     *               flood the audit path.
     *   digest    — keyed hash of the JSON encoding, using a site-local secret
     *               that is never itself recorded. It lets two attempts be
     *               recognised as identical without either being readable, and
     *               MUST NOT be a bare hash: an unkeyed digest of a short or
     *               low-entropy input is reversible by brute force.
     *   shape     — recursive structure with every value removed. Keys are
     *               retained because the key names are the diagnostic value;
     *               keys that name a secret are themselves replaced. Bounded in
     *               depth and in breadth so a nested or wide payload cannot
     *               turn the summarizer into the denial-of-service it is meant
     *               to survive.
     *   redaction — human-readable statement of the guarantee, so an operator
     *               reading a stored row can see what it does and does not hold.
     */
    public function summarize($input): array;
}
