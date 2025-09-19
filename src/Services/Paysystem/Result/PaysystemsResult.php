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

namespace Bitrix24\SDK\Services\Paysystem\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PaysystemsResult
 *
 * @package Bitrix24\SDK\Services\Paysystem\Result
 */
class PaysystemsResult extends AbstractResult
{
    /**
     * @return PaysystemItemResult[]
     * @throws BaseException
     */
    public function getPaysystems(): array
    {
        $paysystems = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $paysystems[] = new PaysystemItemResult($item);
        }

        return $paysystems;
    }
}
