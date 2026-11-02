<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Category\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class CategoriesResult extends AbstractResult
{
    /**
     * @return CategoryItemResult[]
     * @throws BaseException
     */
    public function getCategories(): array
    {
        return array_map(static fn (array $item): CategoryItemResult => new CategoryItemResult($item), $this->getCoreResponse()->getResponseData()->getResult()['categories']);
    }
}
