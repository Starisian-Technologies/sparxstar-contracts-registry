<?php
/**
 * Resource Sensitivity Enum
 *
 * Classifies the sensitivity of a protected resource. The sensitivity
 * level drives the Two-Zone Rule and StepUpPolicy.
 *
 * Provisional: once sparxstar-ouroboros-integrity ships the canonical type,
 * this file MUST be removed and the Ouroboros package version MUST be used.
 *
 * @package    SparxStar\Helios\Agreement\Enums
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Agreement\Enums;

/**
 * Enum SPXResourceSensitivity
 *
 * LEVEL_1 — Low-sensitivity resource. May be served at the Cloudflare
 *            edge (ALLOW_EDGE) when the ContextPulse is valid and no
 *            step-up is required. No origin round-trip needed.
 *
 * LEVEL_2 — Medium-sensitivity resource. The edge may DENY or STEP_UP,
 *            but cannot YES. Only the WordPress origin may issue a final
 *            YES (ALLOW_ORIGIN). Step-up is required when trust_score < 0.7.
 *
 * LEVEL_3 — High-sensitivity resource. The edge may DENY or STEP_UP,
 *            never YES. Always requires step-up; a pulse with trust_level
 *            lower than L3 will trigger STEP_UP. MUST NOT use email 2FA.
 *
 * @package SparxStar\Helios\Agreement\Enums
 * @since   1.0.0
 */
enum SPXResourceSensitivity: int {
	case LEVEL_1 = 1;
	case LEVEL_2 = 2;
	case LEVEL_3 = 3;
}
