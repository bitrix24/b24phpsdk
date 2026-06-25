<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking\WaitlistClient\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class WaitlistClientsResult extends AbstractResult
{
    /**
     * @return WaitlistClientItemResult[]
     * @throws BaseException
     */
    public function getClients(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult()['waitListClient'] as $item) {
            $items[] = new WaitlistClientItemResult($item);
        }

        return $items;
    }
}
