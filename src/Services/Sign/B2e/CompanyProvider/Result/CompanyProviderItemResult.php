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

namespace Bitrix24\SDK\Services\Sign\B2e\CompanyProvider\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read string|null $code Signature provider code
 * @property-read string|null $uid Unique identifier of the provider
 * @property-read string|null $name Provider display name
 * @property-read CarbonImmutable|null $date Provider activation date
 * @property-read CarbonImmutable|null $expires Provider expiration date
 */
class CompanyProviderItemResult extends AbstractItem
{
    public function __get($offset)
    {
        return match ($offset) {
            'date', 'expires' => empty($this->data[$offset])
                ? null
                : CarbonImmutable::parse($this->data[$offset]),
            default => $this->data[$offset] ?? null,
        };
    }
}
