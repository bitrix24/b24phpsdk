<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Status\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * Class StatusItemResult
 *
 * @property-read int $ID
 * @property-read string|null $ENTITY_ID
 * @property-read string|null $STATUS_ID
 * @property-read int|null $SORT
 * @property-read string|null $NAME
 * @property-read string|null $NAME_INIT
 * @property-read bool|null $SYSTEM
 * @property-read int|null $CATEGORY_ID
 * @property-read string|null $COLOR
 * @property-read string|null $SEMANTICS
 * @property-read string|null $EXTRA
 */
class StatusItemResult extends AbstractCrmItem
{
    
}
