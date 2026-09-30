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

namespace Bitrix24\SDK\Services\Main\EventLogField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class EventLogFieldsResult extends AbstractResult
{
    /**
     * @return EventLogFieldItemResult[]
     * @throws BaseException
     */
    public function getEventLogFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new EventLogFieldItemResult($item);
        }

        return $items;
    }

    /**
     * Requires the name field in each descriptor; include name when using a custom select.
     *
     * @return array<string, array<string, mixed>>
     * @throws LogicException
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        $fields = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            if (!isset($item['name']) || !is_string($item['name']) || $item['name'] === '') {
                throw new LogicException('Select the name field to index event log field descriptions.');
            }

            $fields[$item['name']] = $item;
        }

        return $fields;
    }
}
