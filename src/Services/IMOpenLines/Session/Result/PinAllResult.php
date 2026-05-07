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

class PinAllResult extends AbstractResult
{
    /**
     * @return array<int> Array of pinned session IDs
     */
    public function getPinnedSessionIds(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        return array_map('intval', $result);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
