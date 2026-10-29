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

namespace Bitrix24\SDK\Services\CRM\V3\Form\Service;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\FieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\DateTimeFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\StringFieldConditionBuilder;

/**
 * Typed filter for `crm.form.list` (filterable fields: id, code, name, active, createdTime, updatedTime).
 */
class FormFilter extends AbstractFilterBuilder
{
    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    public function code(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('code', $this);
    }

    public function name(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('name', $this);
    }

    /**
     * REST v3 forms validate `active` as a native boolean and reject the Y/N strings
     * produced by BoolFieldConditionBuilder, so pass true/false to eq()/neq().
     */
    public function active(): FieldConditionBuilder
    {
        return new FieldConditionBuilder('active', $this);
    }

    public function createdTime(): DateTimeFieldConditionBuilder
    {
        return new DateTimeFieldConditionBuilder('createdTime', $this);
    }

    public function updatedTime(): DateTimeFieldConditionBuilder
    {
        return new DateTimeFieldConditionBuilder('updatedTime', $this);
    }
}
