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

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\Types\DateTimeFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\StringFieldConditionBuilder;

class EventLogFilter extends AbstractFilterBuilder
{
    // Identifiers

    public function id(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('id', $this);
    }

    // Date/time

    public function timestampX(): DateTimeFieldConditionBuilder
    {
        return new DateTimeFieldConditionBuilder('timestampX', $this);
    }

    // Integer fields

    public function userId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('userId', $this);
    }

    public function guestId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('guestId', $this);
    }

    // String fields

    public function severity(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('severity', $this);
    }

    public function auditTypeId(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('auditTypeId', $this);
    }

    public function moduleId(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('moduleId', $this);
    }

    public function itemId(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('itemId', $this);
    }

    public function remoteAddr(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('remoteAddr', $this);
    }

    public function siteId(): StringFieldConditionBuilder
    {
        return new StringFieldConditionBuilder('siteId', $this);
    }
}
