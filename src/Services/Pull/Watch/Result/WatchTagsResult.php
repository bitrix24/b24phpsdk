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

namespace Bitrix24\SDK\Services\Pull\Watch\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

class WatchTagsResult extends AbstractResult
{
    /**
     * @return array<int, int|string>
     * @throws BaseException
     */
    public function getTags(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}
