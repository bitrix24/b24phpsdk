<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Sign\B2e\PersonalTail\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null $id Signed document identifier
 * @property-read string|null $title Document title
 * @property-read CarbonImmutable|null $signed_date Document signing date
 * @property-read string|null $file_url Download URL for the signed document
 */
class PersonalTailItemResult extends AbstractItem
{
    public function __get($offset)
    {
        return match ($offset) {
            'signed_date' => empty($this->data[$offset])
                ? null
                : CarbonImmutable::parse($this->data[$offset]),
            default => $this->data[$offset] ?? null,
        };
    }
}
