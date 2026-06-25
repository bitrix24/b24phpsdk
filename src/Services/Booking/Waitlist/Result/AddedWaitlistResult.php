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

namespace Bitrix24\SDK\Services\Booking\Waitlist\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemResult;

class AddedWaitlistResult extends AddedItemResult
{
    /**
     * @throws BaseException
     */
    #[\Override]
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}
