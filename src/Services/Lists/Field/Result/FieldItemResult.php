<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Lists\Field\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class FieldItemResult
 *
 * @property-read string|null $FIELD_ID Field identifier
 * @property-read int|null $SORT Sorting order
 * @property-read string|null $NAME Field name
 * @property-read string|null $IS_REQUIRED Required flag (Y/N)
 * @property-read string|null $MULTIPLE Multiple flag (Y/N)
 * @property-read mixed $DEFAULT_VALUE Default value
 * @property-read string|null $TYPE Field type
 * @property-read string|null $PROPERTY_TYPE Property type
 * @property-read mixed $PROPERTY_USER_TYPE User type
 * @property-read string|null $CODE Symbolic code
 * @property-read string|null $ID Field identifier
 * @property-read int|null $LINK_IBLOCK_ID Linked list identifier
 * @property-read string|null $ROW_COUNT Field height
 * @property-read string|null $COL_COUNT Field width
 * @property-read array|null $USER_TYPE_SETTINGS User type settings
 * @property-read array|null $SETTINGS Display settings
 * @property-read array|null $DISPLAY_VALUES_FORM Display values for list type
 */
class FieldItemResult extends AbstractItem
{
}
