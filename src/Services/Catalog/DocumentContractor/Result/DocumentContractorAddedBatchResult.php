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

namespace Bitrix24\SDK\Services\Catalog\DocumentContractor\Result;

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class DocumentContractorAddedBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function documentContractor(): DocumentContractorItemResult
    {
        return new DocumentContractorItemResult($this->responseData->getResult()['documentContractor']);
    }
}
