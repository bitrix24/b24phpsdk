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

namespace Bitrix24\SDK\Services\CRM\Userfield\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException;
use Bitrix24\SDK\Services\CRM\Userfield\Result\UserfieldTypesResult;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;

readonly class UserfieldConstraints
{
    /**
     * @param non-empty-string $userfieldName
     * @return void
     * @throws UserfieldNameIsTooLongException
     * @throws InvalidArgumentException
     */
    public function validName(string $userfieldName): void
    {
        $userfieldName = trim($userfieldName);
        if (empty($userfieldName)) {
            throw new InvalidArgumentException('userfield name can not be empty');
        }

        if (strlen($userfieldName) > 13) {
            throw new UserfieldNameIsTooLongException(
                sprintf(
                    'userfield name «%s» is too long «%s», maximum length - 13 characters',
                    $userfieldName,
                    strlen($userfieldName)
                )
            );
        }
    }
}