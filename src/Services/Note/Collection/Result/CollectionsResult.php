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

namespace Bitrix24\SDK\Services\Note\Collection\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionListCursor;

class CollectionsResult extends AbstractResult
{
    /**
     * @return CollectionItemResult[]
     * @throws BaseException
     */
    public function getCollections(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['items'] as $item) {
            $items[] = new CollectionItemResult($item);
        }

        return $items;
    }

    /**
     * @throws BaseException
     */
    public function getNextCursor(): ?CollectionListCursor
    {
        $cursor = $this->getCoreResponse()->getResponseData()->getResult()['nextCursor'] ?? null;

        return $cursor === null ? null : CollectionListCursor::fromArray($cursor);
    }
}
