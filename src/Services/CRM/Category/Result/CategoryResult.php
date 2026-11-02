<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Category\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class CategoryResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function category(): CategoryItemResult
    {
        return new CategoryItemResult($this->getCoreResponse()->getResponseData()->getResult()['category']);
    }
}
