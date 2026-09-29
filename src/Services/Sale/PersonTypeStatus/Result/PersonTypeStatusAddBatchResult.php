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

namespace Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Class PersonTypeStatusAddBatchResult
 *
 * Result of the sale.businessValuePersonDomain.add command executed in batch mode.
 * The record has no own identifier, so the class does not extend AddedItemBatchResult.
 *
 * @package Bitrix24\SDK\Services\Sale\PersonTypeStatus\Result
 */
class PersonTypeStatusAddBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function isSuccess(): bool
    {
        return isset($this->responseData->getResult()['businessValuePersonDomain']);
    }

    public function personTypeStatus(): PersonTypeStatusItemResult
    {
        return new PersonTypeStatusItemResult($this->responseData->getResult()['businessValuePersonDomain']);
    }
}
