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

namespace Bitrix24\SDK\Services\Main\UserHistory\Service;

use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

final readonly class UserHistoryTailCursor
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private int|string $value,
        private string $field = 'id',
        private SortOrder $order = SortOrder::Ascending,
    ) {
        if (trim($field) === '' || (is_string($value) && trim($value) === '')) {
            throw new InvalidArgumentException('Cursor field and value must not be empty.');
        }

        if ($field === 'id' && (is_int($value) ? $value < 0 : !ctype_digit($value))) {
            throw new InvalidArgumentException('An ID cursor requires a non-negative integer or decimal string.');
        }

        if ($field === 'id' && is_string($value)) {
            $canonicalValue = ltrim($value, '0');
            $canonicalValue = $canonicalValue === '' ? '0' : $canonicalValue;
            if ((string)(int)$canonicalValue !== $canonicalValue) {
                throw new InvalidArgumentException('The ID cursor exceeds the supported integer range.');
            }
        }
    }

    /**
     * The API returns string ID cursors but requires integers in requests.
     *
     * @return array{field: string, value: int|string, order: string}
     */
    public function toArray(): array
    {
        return ['field' => $this->field, 'value' => $this->field === 'id' ? (int)$this->value : $this->value, 'order' => $this->order->value];
    }
}
