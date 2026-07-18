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

namespace Bitrix24\SDK\Services\Timeman\Record\Service;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Filters\Types\DateTimeFieldConditionBuilder;
use Bitrix24\SDK\Filters\Types\IntFieldConditionBuilder;

class RecordFilter extends AbstractFilterBuilder
{
    public function userId(): IntFieldConditionBuilder
    {
        return new IntFieldConditionBuilder('userId', $this);
    }

    public function startTime(): DateTimeFieldConditionBuilder
    {
        return new DateTimeFieldConditionBuilder('startTime', $this);
    }
}
