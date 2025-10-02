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

namespace Bitrix24\SDK\Services\Paysystem\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class PaysystemItemResult
 *
 * @property-read int|null $ID Payment system identifier
 * @property-read string|null $NAME Payment system name
 * @property-read string|null $CODE Payment system code
 * @property-read string|null $DESCRIPTION Payment system description
 * @property-read string|null $ACTIVE Payment system activity status (Y/N)
 * @property-read int|null $SORT Sorting order
 * @property-read string|null $PS_MODE Payment system mode
 * @property-read string|null $ACTION_FILE Action file path
 * @property-read string|null $RESULT_FILE Result file path
 * @property-read string|null $NEW_WINDOW Open in new window flag (Y/N)
 * @property-read string|null $HAVE_PAYMENT Has payment flag (Y/N)
 * @property-read string|null $HAVE_ACTION Has action flag (Y/N)
 * @property-read string|null $HAVE_RESULT Has result flag (Y/N)
 * @property-read string|null $HAVE_PREPAY Has prepayment flag (Y/N)
 * @property-read string|null $HAVE_PRICE Has price flag (Y/N)
 * @property-read string|null $CURRENCY Currency code
 * @property-read array|null $LOGOTIP Payment system logo
 * @property-read string|null $XML_ID External identifier
 * @property-read array|null $SETTINGS Payment system settings
 */
class PaysystemItemResult extends AbstractItem
{
}
