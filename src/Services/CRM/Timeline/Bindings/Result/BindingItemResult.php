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

namespace Bitrix24\SDK\Services\CRM\Timeline\Bindings\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;

/**
 * Class BindingItemResult
 *
 * @property-read int $OWNER_ID
 * @property-read int|null $ENTITY_ID
 * @property-read string|null $ENTITY_TYPE
 */
class BindingItemResult extends AbstractCrmItem
{
}
