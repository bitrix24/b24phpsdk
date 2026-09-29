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

use Bitrix24\SDK\Core\Response\DTO\ResponseData;

class UpdatedUserfieldConfigItemBatchResult
{
    public function __construct(private readonly ResponseData $responseData)
    {
    }

    public function getResponseData(): ResponseData
    {
        return $this->responseData;
    }

    /**
     * userfieldconfig.update returns the full updated field descriptor under the
     * `field` key, same shape as userfieldconfig.add.
     */
    public function field(): UserfieldConfigItemResult
    {
        return new UserfieldConfigItemResult($this->responseData->getResult()['field']);
    }
}
