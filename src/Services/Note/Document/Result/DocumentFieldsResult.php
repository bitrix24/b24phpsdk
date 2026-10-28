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

namespace Bitrix24\SDK\Services\Note\Document\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DocumentFieldsResult extends AbstractResult
{
    /**
     * Include name when selecting metadata fields so descriptors can be indexed.
     *
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     * @throws LogicException
     */
    public function getFieldsDescription(): array
    {
        $fields = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $field) {
            $name = $field['name'] ?? null;
            if (!is_string($name) || $name === '') {
                throw new LogicException('Document field metadata must contain a nonempty string name.');
            }

            $fields[$name] = $field;
        }

        return $fields;
    }

    /**
     * @return DocumentFieldItemResult[]
     * @throws BaseException
     */
    public function getFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new DocumentFieldItemResult($item);
        }

        return $items;
    }
}
