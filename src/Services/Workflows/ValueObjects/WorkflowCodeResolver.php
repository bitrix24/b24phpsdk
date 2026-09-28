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

namespace Bitrix24\SDK\Services\Workflows\ValueObjects;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

final class WorkflowCodeResolver
{
    /** @throws InvalidArgumentException */
    public static function resolveRobotCode(string|RobotCode $code): string
    {
        return ($code instanceof RobotCode ? $code : new RobotCode($code))->getCode();
    }

    /** @throws InvalidArgumentException */
    public static function resolveActivityCode(string|ActivityCode $code): string
    {
        return ($code instanceof ActivityCode ? $code : new ActivityCode($code))->getCode();
    }
}
