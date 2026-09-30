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

use Bitrix24\SDK\Core\Contracts\DeletedItemResultInterface;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class DeletedUserfieldConfigItemBatchResult implements DeletedItemResultInterface
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    /**
     * userfieldconfig.delete returns a null result on success, unlike the boolean
     * `true` returned by most other *.delete methods.
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return $this->responseData->getResult()[0] === null;
    }
}
