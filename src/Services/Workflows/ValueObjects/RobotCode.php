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

final class RobotCode
{
    private const string PATTERN = '/^[a-zA-Z0-9._-]+$/';

    private string $code;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $code)
    {
        if (preg_match(self::PATTERN, $code) !== 1) {
            throw new InvalidArgumentException(sprintf(
                'robot code "%s" is invalid, allowed characters are a-z, A-Z, 0-9, dot, hyphen and underscore',
                $code
            ));
        }

        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
