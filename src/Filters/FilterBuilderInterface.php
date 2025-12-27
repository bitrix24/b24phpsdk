<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Filters;

/**
 * Interface FilterBuilderInterface
 *
 * Contract for all filter builders implementing REST 3.0 filtering logic.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
interface FilterBuilderInterface
{
    /**
     * Convert filter to REST 3.0 array format
     *
     * @return array<int, array|array<string, mixed>>
     */
    public function toArray(): array;

    /**
     * Set raw filter conditions (fallback for edge cases)
     *
     * @param array<int, array> $conditions Raw filter conditions in REST 3.0 format
     * @return static
     */
    public function setRaw(array $conditions): static;
}
