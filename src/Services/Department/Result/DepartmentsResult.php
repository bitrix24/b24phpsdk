<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Services\Department\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class DepartmentsResult
 *
 * @package Bitrix24\SDK\Services\Department\Result
 */
class DepartmentsResult extends AbstractResult
{
    /**
     * @return DepartmentItemResult[]
     * @throws BaseException
     */
    public function getDepartments(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new DepartmentItemResult($item);
        }

        return $items;
    }
}
