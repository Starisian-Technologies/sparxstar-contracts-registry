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
 * The validation phase of a recorded attempt.
 *
 * Two-phase by necessity: `wp_ability_invoked` fires before validation has
 * happened, so the event is written PENDING and resolved afterwards. An event
 * still PENDING at delivery means validation never reported — the request died
 * first. That is a real outcome, not a missing value, and MUST NOT be rewritten
 * to VALID or INVALID.
 */
enum SPXAbilityValidationOutcome: string
{
    /** Recorded, validation has not reported yet. */
    case PENDING = 'pending';

    /** `wp_ability_validate_input` returned true. */
    case VALID = 'valid';

    /** `wp_ability_validate_input` returned a WP_Error. */
    case INVALID = 'invalid';
}
