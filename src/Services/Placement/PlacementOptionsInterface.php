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
 * Marker contract for IM widget placement option builders.
 *
 * An implementation builds the associative array passed as the `OPTIONS`
 * payload to `placement.bind`.
 */
interface PlacementOptionsInterface
{
    /**
     * @return array<string, scalar>
     */
    public function build(): array;
}
