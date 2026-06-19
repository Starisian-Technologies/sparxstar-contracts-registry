<?php
/**
 * SPXIamcEnvelope
 *
 * @since 1.0.0
 *
 * The SPARXSTAR IAM+C Envelope — the serialized identity, access, consent,
 * and context packet issued by Helios and Sirus together.
 *
 * Downstream systems (ESU, DVE, Sky, 3iAtlas) receive this single object
 * instead of making separate calls to Helios identity, consent, and Sirus
 * context APIs. They act on opaque references and derived flags only —
 * no real-world identity (name, email, wp_user_id) ever appears here.
 *
 * Layer:     SPARXSTAR Contextual IAM
 * Object:    SPARXSTAR IAM+C Envelope
 * Components: Helios + Sirus — Locked
 *
 * Wire-safe: to_array() is safe for REST responses and inter-service
 * transport. contributor_ref is the sole identity field.
 *
 * @package    SparxStar\Helios\Envelope
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Envelope;

/**
 * Class SPXIamcEnvelope
 *
 * Immutable value object. Constructed exclusively by SPXIamcEnvelopeBuilder
 * inside the Helios plugin. Consumers type-check against this class via
 * starisian/sparxstar-helios-contracts.
 *
 * @package SparxStar\Helios\Envelope
 * @since   1.0.0
 */
final class SPXIamcEnvelope {

	/**
	 * Schema version for this envelope.
	 *
	 * Increment when fields are added or semantics change.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const SCHEMA_VERSION = '1.0';

	/**
	 * Opaque contributor UUID — the only identity reference that crosses
	 * service boundaries (per ADR-012 / INV-010). Empty for anonymous contexts.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $contributor_ref;

	/**
	 * Platform roles assigned to this contributor.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	public readonly array $roles;

	/**
	 * Correlation ID for distributed tracing.
	 *
	 * Equals the Helios session_id when an active session exists; otherwise
	 * a fresh random token. Propagate this in all downstream audit logs.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $correlation_id;

	/**
	 * Consent tier for this contributor.
	 *
	 * One of: 'adult', 'minor', 'institutional', or '' for anonymous.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $consent_tier;

	/**
	 * Resolved retention class: 'vault' or 'ephemeral'.
	 *
	 * This is the single flag ESU acts on at the door. Fail-closed:
	 * absent or unresolvable consent resolves to 'ephemeral'.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $retention_class;

	/**
	 * Opaque consent record UUID. Propagate for audit without storing
	 * any contributor personal data. All-zeros = no consent on record.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $consent_id;

	/**
	 * Device hash bound to this session.
	 *
	 * HMAC-SHA256 when Helios computed it standalone; Sirus-provided hash
	 * when Sirus is active. Consumers MUST NOT generate device hashes.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $device_hash;

	/**
	 * Source that produced the device hash: 'helios' or 'sirus'.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $device_source;

	/**
	 * Sirus trust signals for this request.
	 *
	 * Keys include: risk (int 0–100), threat (bool), reasons (string[]),
	 * version (string). Empty array when Sirus is not active.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	public readonly array $trust_signals;

	/**
	 * Non-correlating per-release public reference (GAL / ADR-012 amendment).
	 *
	 * Populated only when a release context is present (i.e. DVE is generating
	 * a Release Receipt). Empty string in all other session contexts.
	 *
	 * Public projections MUST use this field instead of contributor_ref or
	 * contributor_id. It is non-correlating across releases — the same
	 * contributor will have a different release_author_ref for each release.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	public readonly string $release_author_ref;

	/**
	 * Unix timestamp when this envelope was issued.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public readonly int $issued_at;

	/**
	 * Schema version string.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public readonly string $version;

	/**
	 * Constructor. Called only by SPXIamcEnvelopeBuilder.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $contributor_ref Opaque contributor UUID.
	 * @param string[]             $roles           Platform roles.
	 * @param string               $correlation_id  Distributed trace token.
	 * @param string               $consent_tier    'adult'|'minor'|'institutional'|''.
	 * @param string               $retention_class 'vault'|'ephemeral'.
	 * @param string               $consent_id      Opaque consent record UUID.
	 * @param string               $device_hash     Device identifier.
	 * @param string               $device_source   'helios'|'sirus'.
	 * @param array<string, mixed> $trust_signals      Sirus signal map.
	 * @param string               $release_author_ref Per-release public ref; empty outside release context.
	 */
	public function __construct(
		string $contributor_ref,
		array $roles,
		string $correlation_id,
		string $consent_tier,
		string $retention_class,
		string $consent_id,
		string $device_hash,
		string $device_source,
		array $trust_signals,
		string $release_author_ref = ''
	) {
		$this->contributor_ref    = $contributor_ref;
		$this->roles              = $roles;
		$this->correlation_id     = $correlation_id;
		$this->consent_tier       = $consent_tier;
		$this->retention_class    = $retention_class;
		$this->consent_id         = $consent_id;
		$this->device_hash        = $device_hash;
		$this->device_source      = $device_source;
		$this->trust_signals      = $trust_signals;
		$this->release_author_ref = $release_author_ref;
		$this->issued_at          = time();
		$this->version            = self::SCHEMA_VERSION;
	}

	/**
	 * Return true if the contributor has VAULT retention class.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_vault(): bool {
		return 'vault' === $this->retention_class;
	}

	/**
	 * Return true if this is an anonymous (unauthenticated) envelope.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_anonymous(): bool {
		return '' === $this->contributor_ref;
	}

	/**
	 * Return true if trust signals indicate an active threat.
	 *
	 * Fail-closed: returns false (not true) when Sirus is absent.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_threat(): bool {
		return ! empty( $this->trust_signals['threat'] );
	}

	/**
	 * Return the Sirus risk score (0–100), or 0 when Sirus is absent.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function risk_score(): int {
		return isset( $this->trust_signals['risk'] ) ? (int) $this->trust_signals['risk'] : 0;
	}

	/**
	 * Serialise to array for wire transport and audit logs.
	 *
	 * Safe for REST responses and inter-service boundaries. Contains no
	 * real-world identity — contributor_ref is opaque per INV-010.
	 *
	 * @since 1.0.0
	 *
	 * @return array{
	 *   version:         string,
	 *   contributor_ref: string,
	 *   roles:           string[],
	 *   correlation_id:  string,
	 *   consent_tier:    string,
	 *   retention_class: string,
	 *   consent_id:      string,
	 *   device_hash:     string,
	 *   device_source:   string,
	 *   trust_signals:      array<string, mixed>,
	 *   issued_at:          int,
	 *   release_author_ref: string
	 * }
	 */
	public function to_array(): array {
		return array(
			'version'            => $this->version,
			'contributor_ref'    => $this->contributor_ref,
			'roles'              => $this->roles,
			'correlation_id'     => $this->correlation_id,
			'consent_tier'       => $this->consent_tier,
			'retention_class'    => $this->retention_class,
			'consent_id'         => $this->consent_id,
			'device_hash'        => $this->device_hash,
			'device_source'      => $this->device_source,
			'trust_signals'      => $this->trust_signals,
			'issued_at'          => $this->issued_at,
			'release_author_ref' => $this->release_author_ref,
		);
	}
}
