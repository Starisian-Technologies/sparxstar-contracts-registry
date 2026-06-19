<?php
/**
 * RetentionClass Enum
 *
 * Per ADR-013 / INV-009: carrier retention is governed by this class.
 * Positive contributor consent resolves to VAULT; absent or revoked
 * consent resolves fail-closed to EPHEMERAL (carrier destroyed after
 * processing, with an append-only DestructionReceipt).
 *
 * This is the single switch that turns the pipeline from
 * "process and destroy" to "process and keep."
 *
 * @package    SparxStar\Helios\Consent
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Consent;

/**
 * Enum RetentionClass
 *
 * The two possible retention outcomes for a carrier (raw audio, video,
 * handwriting scan). Fail-closed: the absence of a consent record, a
 * revoked record, or any resolution failure yields EPHEMERAL.
 *
 * @package SparxStar\Helios\Consent
 * @since   1.0.0
 */
enum SPXRetentionClass: string {

	/**
	 * Carrier is retained in the vault.
	 *
	 * Requires: technical_consent = true AND purposes['storage'] = true.
	 */
	case VAULT = 'vault';

	/**
	 * Carrier is destroyed after processing.
	 *
	 * Default for any absent, revoked, or unresolvable consent. A
	 * DestructionReceipt is appended to the contribution record.
	 */
	case EPHEMERAL = 'ephemeral';

	/**
	 * Return the fail-closed default.
	 *
	 * Any code path that cannot positively resolve consent must call this.
	 *
	 * @since 1.0.0
	 * @return self Always EPHEMERAL.
	 */
	public static function fail_closed(): self {
		return self::EPHEMERAL;
	}

	/**
	 * Resolve the retention class from explicit consent flags.
	 *
	 * VAULT requires both technical consent and storage-purpose consent.
	 * Any other combination resolves fail-closed to EPHEMERAL.
	 *
	 * @since 1.0.0
	 *
	 * @param bool                 $technical_consent True if the contributor agreed to data processing.
	 * @param array<string, bool>  $purpose_consent   Map of purpose slug to bool.
	 * @return self
	 */
	public static function from_consent_flags( bool $technical_consent, array $purpose_consent ): self {
		if ( $technical_consent && ! empty( $purpose_consent['storage'] ) ) {
			return self::VAULT;
		}

		return self::EPHEMERAL;
	}
}
