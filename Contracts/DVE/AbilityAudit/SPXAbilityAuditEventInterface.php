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
 * One recorded attempt to invoke a WordPress ability.
 *
 * An "attempt" is not an invocation. The event is emitted from
 * `wp_ability_invoked`, which WordPress fires BEFORE the permission callback
 * and BEFORE input validation. An event therefore proves only that a caller
 * asked; it never proves the call was permitted, valid, or executed.
 *
 * Every implementation MUST satisfy SPXAbilityAttemptSummarizerInterface for
 * the summary it attaches. No implementation may persist raw ability input.
 */
interface SPXAbilityAuditEventInterface
{
    /** Unique identity of this event. Redelivery MUST be idempotent on it. */
    public function eventId(): string;

    /**
     * Groups the phases of one attempt: the invocation and its later
     * validation outcome. Distinct per invocation, never reused, and stable
     * across the nested invocation of one ability by another.
     */
    public function correlationId(): string;

    /**
     * Fully-qualified ability name, including its owning namespace.
     *
     * A recorder MUST ignore any ability outside its own namespace.
     * `wp_ability_invoked` is global, so a recorder that does not filter will
     * record every other plugin's abilities and the platform audit will
     * double-count. Ownership is assigned by domain.
     */
    public function abilityName(): string;

    /**
     * Opaque reference to the caller. MUST NOT be an email address, login
     * name, or any other directly identifying value.
     */
    public function actorRef(): string;

    /** Multisite network id, or 0 on a single-site install. */
    public function networkId(): int;

    /** Site id within the network. */
    public function blogId(): int;

    /** Schema version of this event's shape. */
    public function eventVersion(): int;

    /**
     * Validation phase of the attempt.
     *
     * Emitted as PENDING, then resolved once `wp_ability_validate_input`
     * reports. A recorder observing that filter MUST return the incoming
     * verdict unchanged: those filters assert, and telemetry must never
     * influence whether input is accepted.
     */
    public function validationOutcome(): SPXAbilityValidationOutcome;

    /**
     * Shape-only description of the input.
     *
     * @return array<string,mixed> Conforming to SPXAbilityAttemptSummarizerInterface.
     */
    public function summary(): array;
}
