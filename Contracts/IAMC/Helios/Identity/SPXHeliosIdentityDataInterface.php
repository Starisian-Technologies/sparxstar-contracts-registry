<?php
/**
 * Helios Identity Data Interface
 *
 * The cross-service identity contract. This is the ONLY identity surface that
 * any platform service (ESU, DVE, Dheghom, 3iAtlas) is permitted to consume.
 *
 * Per ADR-012 / INV-010: the wp_user_id (account_id) never leaves the Helios
 * auth layer. External consumers receive only the opaque contributor_ref UUID.
 * WordPress-specific fields (user_id, user_login, user_email, display_name)
 * are deliberately absent from this interface.
 *
 * Implemented by HeliosIdentityContext in the Helios WordPress plugin.
 * Consumer repos type-check against this interface exclusively.
 *
 * @package    SparxStar\Helios\Identity
 * @since      1.0.0
 * @author     Starisian Technologies
 * @license    GPL-2.0-or-later
 */

declare(strict_types=1);

namespace SparxStar\Helios\Identity;

/**
 * Interface SPXHeliosIdentityDataInterface
 *
 * Exposes only the fields that are safe to cross service boundaries.
 *
 * @package SparxStar\Helios\Identity
 * @since   1.0.0
 */
interface SPXHeliosIdentityDataInterface {

	/**
	 * Return the opaque contributor UUID.
	 *
	 * This is the sole identity reference permitted outside the Helios auth
	 * layer (per ADR-012 / INV-010). An empty string indicates an anonymous
	 * or unauthenticated context.
	 *
	 * @since 1.0.0
	 * @return string UUID v4, or empty string for anonymous contexts.
	 */
	public function getContributorRef(): string;

	/**
	 * Return the request correlation ID.
	 *
	 * Equals the session_id when a Helios session exists, otherwise a fresh
	 * random UUID. Propagate this in distributed traces and audit logs.
	 *
	 * @since 1.0.0
	 * @return string Non-empty correlation string.
	 */
	public function getCorrelationId(): string;

	/**
	 * Return the roles assigned to this contributor.
	 *
	 * Roles are opaque strings. Do not assume WordPress role slugs — future
	 * non-WordPress identities may carry different role vocabularies.
	 *
	 * @since 1.0.0
	 * @return string[]
	 */
	public function getRoles(): array;

	/**
	 * Return the schema version of this identity data object.
	 *
	 * Consumers should check this before accessing version-gated fields.
	 *
	 * @since 1.0.0
	 * @return string Semver-style string, e.g. '1.1'.
	 */
	public function getVersion(): string;

	/**
	 * Return the Unix timestamp when this context was created.
	 *
	 * Use for freshness checks and TTL enforcement in edge caches.
	 *
	 * @since 1.0.0
	 * @return int Unix timestamp.
	 */
	public function getIssuedAt(): int;

	/**
	 * Return true if this is an anonymous (unauthenticated) context.
	 *
	 * Anonymous contexts have an empty contributor_ref and user_id = 0.
	 * Use this to branch cleanly rather than checking for empty strings.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function isAnonymous(): bool;
}
