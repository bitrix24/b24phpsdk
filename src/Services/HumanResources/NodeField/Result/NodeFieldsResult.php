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

namespace Bitrix24\SDK\Services\HumanResources\NodeField\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\HumanResources\Result\ResultExtractor;

class NodeFieldsResult extends AbstractResult
{
    use ResultExtractor;

    /**
     * @return NodeFieldItemResult[]
     * @throws BaseException
     */
    public function getNodeFields(): array
    {
        return array_map(
            static fn(array $item): NodeFieldItemResult => new NodeFieldItemResult($item),
            $this->getItemsData()
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     */
    public function getFieldsDescription(): array
    {
        $fields = [];
        foreach ($this->getItemsData() as $item) {
            $fields[(string)$item['name']] = $item;
        }

        return $fields;
    }
}
