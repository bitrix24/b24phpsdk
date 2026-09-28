<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Demos\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Class DemoRegisteredBatchResult
 *
 * Result of landing.demos.register batch command: identifiers of created or updated templates
 *
 * @package Bitrix24\SDK\Services\Landing\Demos\Result
 */
class DemoRegisteredBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    /**
     * Identifiers of created or updated templates
     *
     * @return int[]
     */
    public function getIds(): array
    {
        return array_map(intval(...), array_values($this->responseData->getResult()));
    }
}
