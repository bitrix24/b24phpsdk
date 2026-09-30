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

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig\Result;

use Bitrix24\SDK\Core\Contracts\AddedItemIdResultInterface;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class AddedUserfieldConfigItemBatchResult implements AddedItemIdResultInterface
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    public function field(): UserfieldConfigItemResult
    {
        return new UserfieldConfigItemResult($this->responseData->getResult()['field']);
    }

    #[\Override]
    public function getId(): int
    {
        return $this->field()->id;
    }
}
