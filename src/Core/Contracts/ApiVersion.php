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

namespace Bitrix24\SDK\Core\Contracts;

enum ApiVersion: int
{
    case v1 = 1;
    case v3 = 3;

    public function isV3(): bool
    {
        return $this->value === 3;
    }

    public function isV1(): bool
    {
        return $this->value === 1;
    }
}