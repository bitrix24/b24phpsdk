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

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogUsersResult extends AbstractResult
{
    /**
     * @return DialogUserItemResult[]
     * @throws BaseException
     */
    public function users(): array
    {
        return array_map(
            static fn(array $user): DialogUserItemResult => new DialogUserItemResult($user),
            array_filter($this->getCoreResponse()->getResponseData()->getResult(), 'is_array')
        );
    }

    /**
     * @throws BaseException
     */
    public function total(): int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getTotal() ?? 0;
    }

    /**
     * @throws BaseException
     */
    public function next(): ?int
    {
        return $this->getCoreResponse()->getResponseData()->getPagination()->getNextItem();
    }
}
