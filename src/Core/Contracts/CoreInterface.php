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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Response\Response;

/**
 * Interface CoreInterface
 *
 * @package Bitrix24\SDK\Core\Contracts
 */
interface CoreInterface
{
    /**
     * Make an API call.
     *
     * @param non-empty-string $apiMethod
     * @param array<string, mixed> $parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function call(string $apiMethod, array $parameters = [], ApiVersion $apiVersion = ApiVersion::v1): Response;

    public function getApiClient(): ApiClientInterface;
}