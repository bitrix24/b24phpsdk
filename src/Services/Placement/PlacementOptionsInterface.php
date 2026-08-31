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

namespace Bitrix24\SDK\Services\Placement;

/**
 * Interface for typed placement OPTIONS builders.
 *
 * Implementations build the `OPTIONS` array passed to `placement.bind`.
 *
 * @link https://apidocs.bitrix24.com/api-reference/widgets/placement-bind.html
 */
interface PlacementOptionsInterface
{
    /**
     * Build and return the OPTIONS array for placement.bind.
     *
     * @return array<string, scalar>
     */
    public function build(): array;
}

