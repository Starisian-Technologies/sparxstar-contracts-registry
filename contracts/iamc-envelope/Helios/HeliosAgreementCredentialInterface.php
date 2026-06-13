<?php
/**
 * Helios Agreement Credential Interface
 *
 * Contract for Helios-internal agreement credentials that gate writes to the
 * data access layer (DAL). Any component that performs a privileged write MUST
 * obtain and validate a credential before executing:
 *
 *   if ( ! $credential->isValid() ) {
 *       throw new \RuntimeException( 'Invalid agreement credential' );
 *   }
 *
 * Credentials are session-bound and carry:
 * - session_id  — ties the credential to exactly one Helios session.
 * - device_hash — ties the credential to the device registered for that session.
 * - trust_state — the trust state at the time of issuance.
 *
 * A credential is invalid if:
 * - Its signature cannot be verified.
 * - It has expired.
 * - Its trust_state has been downgraded since issuance (credential is revoked).
 * - The device_hash no longer matches the current device.
 *
 * @package    SparxStar\Helios\Contracts
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface HeliosAgreementCredentialInterface
 *
 * Validates a Helios-internal agreement credential before a DAL write is allowed.
 * Implemented by HeliosAgreementCredential.
 *
 * @package SparxStar\Helios\Contracts
 * @since   1.0.0
 */
interface HeliosAgreementCredentialInterface {

	/**
	 * Determine whether this credential is currently valid.
	 *
	 * A valid credential satisfies ALL of the following:
	 * 1. The token signature is intact (not tampered with).
	 * 2. The token has not expired.
	 * 3. The session referenced by session_id is still active in the DB.
	 * 4. The device_hash in the token matches the current device context.
	 * 5. The trust_state has not been downgraded since issuance.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if all validity checks pass, false otherwise.
	 */
	public function isValid(): bool;

	/**
	 * Return the session ID bound to this credential.
	 *
	 * @since 1.0.0
	 *
	 * @return string Session ID string, or empty string if not set.
	 */
	public function getSessionId(): string;

	/**
	 * Return the device hash bound to this credential.
	 *
	 * @since 1.0.0
	 *
	 * @return string Device hash string, or empty string if not set.
	 */
	public function getDeviceHash(): string;

	/**
	 * Return the trust state recorded at the time this credential was issued.
	 *
	 * @since 1.0.0
	 *
	 * @return string Trust state: NORMAL, STEP_UP_REQUIRED, or LOCKED.
	 */
	public function getTrustState(): string;
}
