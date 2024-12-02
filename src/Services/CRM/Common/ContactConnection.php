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
    public int $contactId;
    public int $sort;
    public bool $isPrimary;

    /**
     * @param positive-int $contactId
     * @param positive-int $sort
     * @param bool $isPrimary
     */
    public function __construct(int $contactId, int $sort = 100, bool $isPrimary = false)
    {
        $this->contactId = $contactId;
        $this->sort = $sort;
        $this->isPrimary = $isPrimary;
    }
}