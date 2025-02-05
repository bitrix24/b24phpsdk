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

namespace Bitrix24\SDK\Application\Workflows\Robots\Common;

/**
 * DTO for store robot result
 */
readonly class RobotResponse
{
    public function __construct(
        public string $eventToken,
        public array $payload,
        public ?string $logMessage
    ) {
    }
}