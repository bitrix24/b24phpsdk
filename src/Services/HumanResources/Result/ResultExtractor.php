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

namespace Bitrix24\SDK\Services\HumanResources\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;

trait ResultExtractor
{
    /**
     * @return array<string, mixed>|array<int, mixed>
     * @throws BaseException
     */
    private function getResultData(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }

    /**
     * @return array<string, mixed>
     * @throws BaseException
     */
    private function getItemData(): array
    {
        $result = $this->getResultData();

        return $result['item'] ?? $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws BaseException
     */
    private function getItemsData(): array
    {
        $result = $this->getResultData();

        return $result['items'] ?? $result;
    }
}
