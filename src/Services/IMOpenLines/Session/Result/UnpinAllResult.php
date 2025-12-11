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

namespace Bitrix24\SDK\Services\IMOpenLines\Session\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class UnpinAllResult extends AbstractResult
{
    /**
     * @return array<int> Array of unpinned session IDs
     */
    public function getUnpinnedSessionIds(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return array_map('intval', $result);
    }
}