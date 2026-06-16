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
 * Factory for reconstructing jobs from their persisted JSON descriptors.
 *
 * Callers supply the fully-qualified class name and the scalar descriptor
 * array returned by SPXJobInterface::getDescriptor(). Implementations are
 * responsible for wiring non-scalar dependencies (e.g. HTTP clients) from
 * the DI container or a service locator.
 *
 * @see SPXJobInterface::getDescriptor()
 */
interface SPXJobFactoryInterface
{
    /**
     * Reconstruct a SPXJobInterface from a persisted descriptor.
     *
     * @param class-string         $class      Fully-qualified job class name.
     * @param array<string, mixed> $descriptor Scalar descriptor from SPXJobInterface::getDescriptor().
     *
     * @throws \InvalidArgumentException If the class is unknown or the descriptor is invalid.
     */
    public function make(string $class, array $descriptor): SPXJobInterface;
}
