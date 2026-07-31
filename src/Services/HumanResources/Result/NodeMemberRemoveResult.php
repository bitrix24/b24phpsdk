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
use Bitrix24\SDK\Core\Result\AbstractResult;

class NodeMemberRemoveResult extends AbstractResult
{
    use ResultExtractor;

    /**
     * @return int[]
     * @throws BaseException
     */
    public function getRemoved(): array
    {
        return $this->getResultData()['removed'] ?? [];
    }

    /**
     * @return array<int, array{userId: int, reason: string}>
     * @throws BaseException
     */
    public function getFailed(): array
    {
        return $this->getResultData()['failed'] ?? [];
    }
}
