<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sale\Delivery\Result;

use Bitrix24\SDK\Core\Result\AddedItemBatchResult;

/**
 * Class DeliveryAddBatchResult
 *
 * Result of the sale.delivery.add command executed in batch mode
 *
 * @package Bitrix24\SDK\Services\Sale\Delivery\Result
 */
class DeliveryAddBatchResult extends AddedItemBatchResult
{
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getResponseData()->getResult()['parent']['ID'];
    }

    public function getParent(): DeliveryItemResult
    {
        return new DeliveryItemResult($this->getResponseData()->getResult()['parent']);
    }

    /**
     * @return DeliveryItemResult[]
     */
    public function getProfiles(): array
    {
        $items = [];
        $result = $this->getResponseData()->getResult();

        if (isset($result['profiles']) && is_array($result['profiles'])) {
            foreach ($result['profiles'] as $item) {
                $items[] = new DeliveryItemResult($item);
            }
        }

        return $items;
    }
}
