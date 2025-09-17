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

namespace Bitrix24\SDK\Services\Sale\TradePlatform\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class TradePlatformsResult
 * @package Bitrix24\SDK\Services\Sale\TradePlatform\Result
 */
class TradePlatformsResult extends AbstractResult
{
    /**
     * Get array of trade platform items
     *
     * @return TradePlatformItemResult[]
     */
    public function getTradePlatforms(): array
    {
        $items = [];
        $tradePlatforms = $this->getCoreResponse()->getResponseData()->getResult()['tradePlatforms'] ?? [];

        foreach ($tradePlatforms as $tradePlatform) {
            $items[] = new TradePlatformItemResult($tradePlatform);
        }

        return $items;
    }

    /**
     * Get total count of trade platforms
     */
    public function getTotal(): int
    {
        return (int)($this->getCoreResponse()->getResponseData()->getResult()['total'] ?? 0);
    }
}
