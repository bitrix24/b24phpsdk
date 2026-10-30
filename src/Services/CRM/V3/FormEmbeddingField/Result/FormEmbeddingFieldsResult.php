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

namespace Bitrix24\SDK\Services\CRM\V3\FormEmbeddingField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class FormEmbeddingFieldsResult extends AbstractResult
{
    /**
     * @return FormEmbeddingFieldItemResult[]
     * @throws BaseException
     */
    public function getFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new FormEmbeddingFieldItemResult($item);
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
                throw new LogicException('Select the name field to index CRM form embedding field descriptions.');
            }

            $fields[$item['name']] = $item;
        }

        return $fields;
    }
}
