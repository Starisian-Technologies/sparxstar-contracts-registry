<?php
/**
 * Sirus Client Interface
 *
 * Contract for consuming context and signals from the SPARXSTAR Sirus Context
 * plugin (https://github.com/Starisian-Technologies/sparxstar-sirus-context).
 *
 * Helios CONSUMES from Sirus; it never WRITES to Sirus. Specifically:
 * - Device context: Sirus provides enriched device data; Helios binds sessions
 *   to the device_hash, but does not generate the hash itself when Sirus is
 *   present.
 * - Signals: Sirus computes network/behavioral signals; Helios passes them to
 *   TrustEngine for evaluation. Helios does NOT compute signals internally.
 *
 * Enforcement boundary: Helios enforces; Sirus observes. No session mutation
 * or trust state change may originate inside a SirusClientInterface
 * implementation.
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
 * Interface SirusClientInterface
 *
 * Implemented by SirusAdapter, which wraps the Sirus Context plugin's API
 * via WordPress filters. Any class implementing this interface MUST NOT:
 * - Mutate session state.
 * - Write trust state to any store.
 * - Accept or provide identity data (identity is Helios-only).
 *
 * @package SparxStar\Helios\Contracts
 * @since   1.0.0
 */
interface SPXSirusClientInterface {

	/**
	 * Return the device context provided by Sirus.
	 *
	 * The returned array MUST contain at minimum:
	 *   - `device_hash`  (string) Sirus-computed device identifier.
	 *   - `source`       (string) Always 'sirus'.
	 *   - `version`      (string) Schema version, e.g. '1.0'.
	 *
	 * Additional Sirus-specific fields (device_id, platform, etc.) may be
	 * present and are passed through transparently for logging purposes, but
	 * Helios only binds sessions to `device_hash`.
	 *
	 * @since 1.0.0
	 *
	 * @throws \RuntimeException If Sirus cannot provide device context.
	 * @return array{device_hash: string, source: string, version: string} Device context from Sirus.
	 */
	public function getDeviceContext(): array;

	/**
	 * Return the current request signals computed by Sirus.
	 *
	 * Signals are opaque to Helios — TrustEngine is responsible for
	 * interpreting them. The returned array SHOULD contain at minimum:
	 *   - `risk`     (int 0–100) Overall risk score.
	 *   - `threat`   (bool)      Whether a threat was detected.
	 *   - `reasons`  (string[])  Pipe-delimited reason codes.
	 *   - `version`  (string)    Schema version.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string,mixed> Signal data keyed by signal name.
	 */
	public function getSignals(): array;
}
