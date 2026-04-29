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

namespace Bitrix24\SDK\Services\IM\User\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\IM\User\UserStatusType;

/**
 * @property-read UserStatusType|null $STATUS
 */
class UserStatusItemResult extends AbstractAnnotatedItem
{
    #[\Override]
    public function __get($offset)
    {
        return match ($offset) {
            'STATUS' => $this->getStatus(),
            default => $this->data[$offset] ?? null,
        };
    }

    public function getStatus(): ?UserStatusType
    {
        $status = $this->data['STATUS'] ?? null;
        if (!is_string($status) || $status === '') {
            return null;
        }

        return UserStatusType::from($status);
    }
}
