<?php
/**
 * ConsentTier Enum
 *
 * Classifies the consent authority model for a contributor. The tier governs
 * which consent pathway applies and what data is automatically ephemeral
 * regardless of the consent record.
 *
 * Per OQ-012 resolution: minor-tier contributors require guardian or
 * institutional consent before any VAULT retention class can be granted.
 * Auto-ephemeral is the forced outcome until consent is confirmed.
 *
 * @package    SparxStar\Helios\Consent
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Consent;

/**
 * Enum ConsentTier
 *
 * ADULT         — Standard contributor. Individual self-consent is valid.
 *                 ConsentResolver honours consent records at face value.
 *
 * MINOR         — Student contributor. Guardian or institutional consent is
 *                 required. ConsentResolver auto-overrides to EPHEMERAL until
 *                 consent_confirmed = true on the enrollment record.
 *
 * INSTITUTIONAL — Bulk enrollment via signed institutional contract. Consent
 *                 authority rests with the institution (not the individual).
 *                 Migrate_to_self() promotes to ADULT when the contributor
 *                 reaches legal age and re-consents individually.
 *
 * @package SparxStar\Helios\Consent
 * @since   1.0.0
 */
enum SPXConsentTier: string {
	case ADULT         = 'adult';
	case MINOR         = 'minor';
	case INSTITUTIONAL = 'institutional';

	/**
	 * Return the default tier for new contributors.
	 *
	 * @since 1.0.0
	 * @return self Always ADULT.
	 */
	public static function default(): self {
		return self::ADULT;
	}

	/**
	 * Return true if this tier requires external (non-self) consent authority.
	 *
	 * MINOR and INSTITUTIONAL both require confirmed external consent before
	 * VAULT retention can be granted.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function requires_external_consent(): bool {
		return self::ADULT !== $this;
	}
}
