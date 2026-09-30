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

namespace Bitrix24\SDK\Services\SonetGroup\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class SonetGroupUsersResult extends AbstractResult
{
    /**
     * @return SonetGroupUserItemResult[]
     * @throws BaseException
     */
    public function getUsers(): array
    {
        return array_map(
            static fn (array $item): SonetGroupUserItemResult => new SonetGroupUserItemResult($item),
            $this->getCoreResponse()->getResponseData()->getResult()
        );
    }
}
