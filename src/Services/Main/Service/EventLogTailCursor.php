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

namespace Bitrix24\SDK\Services\Main\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;

class EventLogTailCursor
{
    public function __construct(
        private readonly int       $value,
        private readonly string    $field = 'id',
        private readonly SortOrder $order = SortOrder::Ascending,
        private readonly int       $limit = 50,
    ) {
    }

    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'order' => $this->order->value,
            'value' => $this->value,
            'limit' => $this->limit,
        ];
    }
}
