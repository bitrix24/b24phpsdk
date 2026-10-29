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

namespace Bitrix24\SDK\Services\Catalog\V3\Measure\Service;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\FieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\StringFieldConditionBuilder;

/**
 * Typed filter for `catalog.measure.list`.
 */
class MeasureFilter extends AbstractFilterBuilder
{
    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    public function code(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('code', $this);
    }

    public function measureTitle(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('measureTitle', $this);
    }

    public function symbol(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('symbol', $this);
    }

    public function symbolIntl(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('symbolIntl', $this);
    }

    public function symbolLetterIntl(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('symbolLetterIntl', $this);
    }

    /**
     * REST v3 catalog validates `isDefault` as a native boolean and rejects the Y/N strings
     * produced by BoolFieldConditionBuilder, so pass true/false to eq()/neq().
     */
    public function isDefault(): FieldConditionBuilder
    {
        return new FieldConditionBuilder('isDefault', $this);
    }
}
