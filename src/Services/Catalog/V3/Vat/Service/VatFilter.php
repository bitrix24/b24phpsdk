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

namespace Bitrix24\SDK\Services\Catalog\V3\Vat\Service;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\FieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\DateTimeFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\StringFieldConditionBuilder;

/**
 * Typed filter for `catalog.vat.list`; filter by the float `rate` field with setRaw() or addCondition().
 */
class VatFilter extends AbstractFilterBuilder
{
    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    public function timestampX(): DateTimeFieldConditionBuilder
    {
        return new DateTimeFieldConditionBuilder('timestampX', $this);
    }

    /**
     * REST v3 catalog validates `active` as a native boolean and rejects the Y/N strings
     * produced by BoolFieldConditionBuilder, so pass true/false to eq()/neq().
     */
    public function active(): FieldConditionBuilder
    {
        return new FieldConditionBuilder('active', $this);
    }

    public function sort(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('sort', $this);
    }

    public function name(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('name', $this);
    }
}
