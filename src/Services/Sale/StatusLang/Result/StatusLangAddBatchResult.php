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

namespace Bitrix24\SDK\Services\Sale\StatusLang\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

/**
 * Class StatusLangAddBatchResult
 *
 * Result of the sale.statusLang.add command executed in batch mode.
 * Status localization has no own identifier, so the class does not extend AddedItemBatchResult.
 *
 * @package Bitrix24\SDK\Services\Sale\StatusLang\Result
 */
class StatusLangAddBatchResult
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
        return isset($this->responseData->getResult()['statusLang']);
    }

    public function statusLang(): StatusLangItemResult
    {
        return new StatusLangItemResult($this->responseData->getResult()['statusLang']);
    }
}
