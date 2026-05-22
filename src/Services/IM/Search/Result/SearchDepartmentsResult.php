<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Search\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SearchDepartmentsResult extends AbstractResult
{
    /**
     * @return SearchDepartmentItemResult[]
     * @throws BaseException
     */
    public function items(): array
    {
        return array_values(array_map(
            static fn(array $item): SearchDepartmentItemResult => new SearchDepartmentItemResult($item),
            array_filter($this->getCoreResponse()->getResponseData()->getResult(), 'is_array')
        ));
    }

    /**
     * @throws BaseException
     */
    public function total(): int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }

    /**
     * @throws BaseException
     */
    public function next(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getNextItem();
    }
}
