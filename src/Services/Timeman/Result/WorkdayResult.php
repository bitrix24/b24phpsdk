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

namespace Bitrix24\SDK\Services\Timeman\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result wrapping a single workday object returned by timeman.open, timeman.pause, timeman.close, timeman.status.
 */
class WorkdayResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function getWorkday(): WorkdayItemResult
    {
        return new WorkdayItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}

