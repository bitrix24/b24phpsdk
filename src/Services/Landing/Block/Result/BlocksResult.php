<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Landing\Block\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class BlocksResult extends AbstractResult
{
    /**
     * @return BlockItemResult[]
     * @throws BaseException
     */
    public function getBlocks(): array
    {
        $res = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $block) {
            $res[] = new BlockItemResult($block);
        }

        return $res;
    }
}
