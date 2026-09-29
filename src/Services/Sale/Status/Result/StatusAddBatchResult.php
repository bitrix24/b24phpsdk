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

namespace Bitrix24\SDK\Services\Sale\Status\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Class StatusAddBatchResult
 *
 * Result of the sale.status.add command executed in batch mode.
 * Status identifier is a string code, so the class does not extend AddedItemBatchResult.
 *
 * @package Bitrix24\SDK\Services\Sale\Status\Result
 */
class StatusAddBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function getId(): string
    {
        return (string)$this->responseData->getResult()['status']['id'];
    }

    public function status(): StatusItemResult
    {
        return new StatusItemResult($this->responseData->getResult()['status']);
    }
}
