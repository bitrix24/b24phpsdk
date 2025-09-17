<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\Userfield\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Task\Userfield\Exceptions\UserfieldNameIsTooLongException;

readonly class UserfieldConstraints
{
    /**
     * @param non-empty-string $userfieldName
     * @throws UserfieldNameIsTooLongException
     * @throws InvalidArgumentException
     */
    public function validName(string $userfieldName): void
    {
        $userfieldName = trim($userfieldName);
        if ($userfieldName === '' || $userfieldName === '0') {
            throw new InvalidArgumentException('userfield name can not be empty');
        }

        if (strlen($userfieldName) > 17) {
            throw new UserfieldNameIsTooLongException(
                sprintf(
                    'userfield name «%s» is too long «%s», maximum length - 17 characters',
                    $userfieldName,
                    strlen($userfieldName)
                )
            );
        }
    }
}
