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

namespace Bitrix24\SDK\Services\IM\Chat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ChatUserListResult extends AbstractResult
{
    /**
     * @return int[]
     * @throws BaseException
     */
    public function getUserIds(): array
    {
        return array_map(
            static fn(mixed $id): int => (int)$id,
            $this->getCoreResponse()->getResponseData()->getResult()
        );
    }
}
