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

namespace Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class UserHistoryChangeFieldsResult extends AbstractResult
{
    /**
     * @return UserHistoryChangeFieldItemResult[]
     * @throws BaseException
     */
    public function getUserHistoryChangeFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new UserHistoryChangeFieldItemResult($item);
        }

        return $items;
    }

    /**
     * Return the original metadata keyed by name, without changing API field types.
     * Include name in a partial selection to use this accessor.
     *
     * @return array<string, array<string, mixed>>
     * @throws BaseException When a descriptor has no selected name.
     */
    public function getFieldsDescription(): array
    {
        $fields = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            if (!is_string($item['name'] ?? null) || $item['name'] === '') {
                throw new BaseException('The field name must be selected to get keyed field descriptions.');
            }

            $fields[$item['name']] = $item;
        }

        return $fields;
    }
}
