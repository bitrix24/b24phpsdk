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

namespace Bitrix24\SDK\Services\SonetGroup\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class SonetGroupResult
 *
 * @package Bitrix24\SDK\Services\SonetGroup\Result
 */
class SonetGroupResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getGroup(): SonetGroupGetItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return new SonetGroupGetItemResult($result);
    }
}
