<?php
/**
 * ConsentReference Value Object
 *
 * Immutable snapshot of a resolved consent decision. This is what
 * ConsentResolver returns to callers (ESU, Sky) and what the
 * /helios/v1/consent/resolve endpoint serialises in its response.
 *
 * Fields:
 *   consent_id       — Opaque UUID. ESU logs this for audit without
 *                       needing to store any contributor personal data.
 *   technical_consent — True if the contributor agreed to data processing
 *                       at all (the top-level legal gate).
 *   purpose_consent  — Per-purpose map (e.g. storage, training, research).
 *                       Keys are platform-defined slugs; values are bool.
 *   retention_class  — Derived outcome: VAULT or EPHEMERAL.
 *                       This is the single flag ESU acts on at the door.
 *   resolved_at      — Unix timestamp of when this reference was created.
 *
 * Fail-closed null object: ConsentReference::ephemeral_null() produces a
 * fully valid reference with retention_class = EPHEMERAL and all consent
 * flags false. Use this when no consent record exists.
 *
 * @package    SparxStar\Helios\Consent
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Consent;

/**
 * Class SPXConsentReference
 *
 * Read-only value object returned by ConsentResolver.
 *
 * @package SparxStar\Helios\Consent
 * @since   1.0.0
 */
final class SPXConsentReference {

	/**
	 * Purpose slug: persistent carrier storage in the vault.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const PURPOSE_STORAGE = 'storage';

	/**
	 * Purpose slug: use carrier/contribution for AI/ML training.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const PURPOSE_TRAINING = 'training';

	/**
	 * Purpose slug: include in linguistic research corpus.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const PURPOSE_RESEARCH = 'research';

	/**
	 * Purpose slug: publish in open-access corpus.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const PURPOSE_PUBLICATION = 'publication';

	/**
	 * Opaque consent record UUID. ESU logs this for audit.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $consent_id;

	/**
	 * True if the contributor agreed to top-level data processing.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public readonly bool $technical_consent;

	/**
	 * Per-purpose consent flags.
	 *
	 * Keys are purpose slugs (e.g. 'storage', 'training').
	 * Values are bool. Absent keys are treated as false by allows_purpose().
	 *
	 * @since 1.0.0
	 * @var array<string, bool>
	 */
	public readonly array $purpose_consent;

	/**
	 * Resolved retention class: VAULT or EPHEMERAL.
	 *
	 * This is the single flag ESU and Sky act on at the door.
	 *
	 * @since 1.0.0
	 * @var SPXRetentionClass
	 */
	public readonly SPXRetentionClass $retention_class;

	/**
	 * Unix timestamp when this reference was resolved.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public readonly int $resolved_at;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string              $consent_id        Opaque UUID.
	 * @param bool                $technical_consent Top-level consent flag.
	 * @param array<string, bool> $purpose_consent   Per-purpose flags.
	 * @param SPXRetentionClass   $retention_class   Resolved retention outcome.
	 */
	public function __construct(
		string $consent_id,
		bool $technical_consent,
		array $purpose_consent,
		SPXRetentionClass $retention_class
	) {
		$this->consent_id        = $consent_id;
		$this->technical_consent = $technical_consent;
		$this->purpose_consent   = $purpose_consent;
		$this->retention_class   = $retention_class;
		$this->resolved_at       = time();
	}

	/**
	 * Return a fail-closed null reference with no consent given.
	 *
	 * Use this when no consent record exists for a contributor, or when
	 * the consent system is unavailable. The returned reference has
	 * retention_class = EPHEMERAL and all flags false.
	 *
	 * @since 1.0.0
	 * @return self
	 */
	public static function ephemeral_null(): self {
		return new self(
			'00000000-0000-0000-0000-000000000000',
			false,
			array(),
			SPXRetentionClass::EPHEMERAL
		);
	}

	/**
	 * True if the retention class is VAULT.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_vault(): bool {
		return SPXRetentionClass::VAULT === $this->retention_class;
	}

	/**
	 * True if consent has been given for the specified purpose.
	 *
	 * Absent keys are treated as false (fail-closed per purpose).
	 *
	 * @since 1.0.0
	 *
	 * @param string $purpose Purpose slug (e.g. 'storage', 'training').
	 * @return bool
	 */
	public function allows_purpose( string $purpose ): bool {
		return ! empty( $this->purpose_consent[ $purpose ] );
	}

	/**
	 * Serialise to array for REST responses and audit logs.
	 *
	 * Safe to return to ESU/Sky: contains no contributor personal data.
	 * The consent_id is opaque; the contributor_ref is NOT included here
	 * (it remains in Helios only per INV-010).
	 *
	 * @since 1.0.0
	 *
	 * @return array{
	 *   consent_id:        string,
	 *   technical_consent: bool,
	 *   purpose_consent:   array<string, bool>,
	 *   retention_class:   string,
	 *   resolved_at:       int
	 * }
	 */
	public function to_array(): array {
		return array(
			'consent_id'        => $this->consent_id,
			'technical_consent' => $this->technical_consent,
			'purpose_consent'   => $this->purpose_consent,
			'retention_class'   => $this->retention_class->value,
			'resolved_at'       => $this->resolved_at,
		);
	}
}
