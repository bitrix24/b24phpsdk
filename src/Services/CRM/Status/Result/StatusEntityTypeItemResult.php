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
 * @property-read string $ID
 * @property-read string $NAME
 * @property-read string $PARENT_ID
 * @property-read array $SEMANTIC_INFO
 * @property-read int $ENTITY_TYPE_ID
 * @property-read string $PREFIX
 * @property-read string $FIELD_ATTRIBUTE_SCOPE
 * @property-read int $CATEGORY_ID
 * @property-read bool $IS_ENABLED
 */
class StatusEntityTypeItemResult extends AbstractCrmItem
{
}
