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

namespace Bitrix24\SDK\Services\Documentgenerator\Region\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class RegionsResult
 *
 * @package Bitrix24\SDK\Services\Documentgenerator\Region\Result
 */
class RegionsResult extends AbstractResult
{
    /**
     * @return RegionItemResult[]
     * @throws BaseException
     */
    public function getRegions(): array
    {
        $items = [];
        $source = [];

        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['regions']) && is_array($result['regions'])) {
            $source = $result['regions'];
        }

        foreach ($source as $item) {
            $items[] = new RegionItemResult($item);
        }

        return $items;
    }
}

