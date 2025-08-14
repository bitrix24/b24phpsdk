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

namespace Bitrix24\SDK\Services\Task\Userfield\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $ID
 * @property-read string $ENTITY_ID
 * @property-read string $FIELD_NAME
 * @property-read string $USER_TYPE_ID
 * @property-read string $XML_ID
 * @property-read int $SORT
 * @property-read bool $MULTIPLE
 * @property-read bool $MANDATORY
 * @property-read bool $SHOW_FILTER
 * @property-read bool $SHOW_IN_LIST
 * @property-read bool $EDIT_IN_LIST
 * @property-read bool $IS_SEARCHABLE
 * @property-read array $EDIT_FORM_LABEL
 * @property-read array $LIST_COLUMN_LABEL
 * @property-read array $LIST_FILTER_LABEL
 * @property-read string $ERROR_MESSAGE
 * @property-read string $HELP_MESSAGE
 * @property-read array $LIST
 * @property-read array $SETTINGS
 */
class UserfieldItemResult extends AbstractItem
{
    //task userfield name prefix UF_
    private const TASK_USERFIELD_PREFIX_LENGTH = 3;

    /**
     * get userfield name without prefix UF_
     */
    public function getOriginalFieldName(): string
    {
        return substr($this->FIELD_NAME, self::TASK_USERFIELD_PREFIX_LENGTH);
    }
}
