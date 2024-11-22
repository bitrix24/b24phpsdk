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

namespace Bitrix24\SDK\Services\CRM\Enum\Result;

use Bitrix24\SDK\Services\CRM\Activity\ActivityStatus;
use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * @property-read int $ID
 * @property-read string $NAME
 * @property-read string $SYMBOL_CODE
 * @property-read string $SYMBOL_CODE_SHORT
 * @property-read ActivityStatus $ENUM
 */
class ActivityStatusItemResult extends AbstractCrmItem
{
    public function __get($offset)
    {
        return match ($offset) {
            'ENUM' => ActivityStatus::from($this->data['ID']),
            default => parent::__get($offset),
        };
    }
}