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
 * Class RegionResult
 *
 * @package Bitrix24\SDK\Services\Documentgenerator\Region\Result
 */
class RegionResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function region(): RegionItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        if (!empty($result['region']) && is_array($result['region'])) {
            $result = $result['region'];
        }

        return new RegionItemResult($result);
    }
}
