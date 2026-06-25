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

namespace Bitrix24\SDK\Services\Timeman\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Represents a single settings item returned by timeman.settings method.
 *
 * @property-read bool $UF_TIMEMAN
 * @property-read bool $UF_TM_FREE
 * @property-read string $UF_TM_MAX_START
 * @property-read string $UF_TM_MIN_FINISH
 * @property-read string $UF_TM_MIN_DURATION
 * @property-read string $UF_TM_ALLOWED_DELTA
 * @property-read bool|null $ADMIN
 */
class TimemanSettingsItemResult extends AbstractAnnotatedItem
{
}

