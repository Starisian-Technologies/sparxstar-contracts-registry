<?php
/**
 * Helios Client Interface
 *
 * The single shared contract that decouples all Helios consumers from the
 * concrete client implementation. Every integration point — REST, AJAX,
 * GraphQL, Sky entry, Release/Sieve entry, DAL writes — MUST depend only
 * on this interface, never on a concrete class.
 *
 * Shared Rules:
 * - No identity originates outside Helios.
 * - Device context is consumed (not generated) by callers.
 * - Trust state is read-only from the caller's perspective; transitions
 *   happen exclusively inside TrustEngine.
 * - Every enforcement point checks validateSession() first.
 *
 * @package    SparxStar\Helios\Contracts
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Contracts;

use Starisian\Sparxstar\Infrastructure\DTOs\AgreementResult;
use Starisian\Sparxstar\Infrastructure\DTOs\ContextPulse;
use Starisian\Sparxstar\Infrastructure\DTOs\ZonePrimitive;
use SparxStar\Helios\Agreement\Enums\SPXResourceSensitivity;
use SparxStar\Helios\Identity\SPXHeliosIdentityDataInterface;

/**
 * Interface SPXHeliosClientInterface
 *
 * The authoritative contract for Helios identity, device, trust, and session
 * operations. Implemented by HeliosClient; consumed by enforcement middleware,
 * integration adapters, and enforcement-point hooks throughout the platform.
 *
 * @package SparxStar\Helios\Contracts
 * @since   1.0.0
 */
interface SPXHeliosClientInterface {

	/**
	 * Evaluate whether the request may proceed (PAM-001 §8).
	 *
	 * The $now parameter replaces any internal time() call, making the
	 * evaluator fully testable. The $zone parameter drives the
	 * ALLOW_EDGE vs ALLOW_ORIGIN decision per spec Section 7.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed               $proof       Governance proof (reserved; pass null if not applicable).
	 * @param ContextPulse|null   $pulse       The ContextPulse for this request (null = absent).
	 * @param SPXResourceSensitivity $sensitivity The sensitivity of the requested resource.
	 * @param int                 $now         Current Unix timestamp.
	 * @param ZonePrimitive       $zone        Evaluation zone (EDGE or ORIGIN).
	 * @return AgreementResult Exactly one of ALLOW_EDGE, ALLOW_ORIGIN, STEP_UP, DENY.
	 */
	public function evaluate(
		mixed $proof,
		?ContextPulse $pulse,
		SPXResourceSensitivity $sensitivity,
		int $now,
		ZonePrimitive $zone
	): AgreementResult;

	/**
	 * Return the authoritative identity context for the current request.
	 *
	 * Identity ALWAYS originates in Helios. Sirus (or any other plugin) MUST
	 * NOT provide or override identity. Throws if no authenticated user is
	 * present — callers must call validateSession() first.
	 *
	 * The returned object exposes only the fields safe for cross-service use
	 * (contributor_ref, correlation_id, roles). WordPress-internal fields
	 * (user_id, user_email) are not accessible via this interface.
	 *
	 * @since 1.0.0
	 *
	 * @throws \RuntimeException If identity is missing or the user cannot be loaded.
	 * @return SPXHeliosIdentityDataInterface Immutable cross-service identity contract.
	 */
	public function getIdentityContext(): SPXHeliosIdentityDataInterface;

	/**
	 * Return the device context for the current request.
	 *
	 * In standalone mode (Sirus absent) the device context is derived from
	 * the local Device_Fingerprint. When Sirus is present and registered via
	 * the `helios_sirus_device_context` filter, Sirus's richer context is
	 * used instead. Callers MUST NOT generate device hashes themselves.
	 *
	 * The returned array is guaranteed to contain:
	 *   - `device_hash`  (string) HMAC-SHA256 or Sirus-provided hash.
	 *   - `source`       (string) 'helios' | 'sirus'.
	 *   - `version`      (string) Schema version, e.g. '1.0'.
	 *
	 * @since 1.0.0
	 *
	 * @throws \RuntimeException If device context cannot be determined.
	 * @return array{device_hash: string, source: string, version: string} Device context.
	 */
	public function getDeviceContext(): array;

	/**
	 * Return the current trust state for the authenticated session.
	 *
	 * Trust state is the exclusive responsibility of TrustEngine. This method
	 * is a read-only projection of the state stored in the session record.
	 *
	 * @since 1.0.0
	 *
	 * @return string One of TrustEngine::STATE_NORMAL, STATE_STEP_UP_REQUIRED,
	 *                or STATE_LOCKED.
	 */
	public function getTrustState(): string;

	/**
	 * Validate the current Helios session.
	 *
	 * Performs all session checks:
	 *  1. JWT cookie present, signature valid, not expired.
	 *  2. Session row exists in DB (not revoked).
	 *  3. Device hash matches the hash bound to the session.
	 *
	 * Returns false (never throws) so callers can branch cleanly.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the session is fully valid, false on any failure.
	 */
	public function validateSession(): bool;

	/**
	 * Trigger a step-up authentication challenge for the current session.
	 *
	 * Transitions the trust state to STEP_UP_REQUIRED via TrustEngine and
	 * revokes the current token. The caller is responsible for sending the
	 * appropriate response (redirect, JSON error, etc.) after calling this.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function requireStepUp(): void;
}
