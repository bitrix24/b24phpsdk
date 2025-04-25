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

namespace Bitrix24\SDK\Services\CRM\Common;

readonly class ContactConnection
{
    /**
     * @param positive-int $contactId
     * @param positive-int $sort
     */
    public function __construct(public int $contactId, public int $sort = 100, public bool $isPrimary = false)
    {
    }
}