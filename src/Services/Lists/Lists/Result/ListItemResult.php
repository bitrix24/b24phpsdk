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

namespace Bitrix24\SDK\Services\Lists\Lists\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class ListItemResult
 *
 * @property-read int|null $ID List identifier
 * @property-read string|null $IBLOCK_TYPE_ID Information block type identifier
 * @property-read string|null $IBLOCK_CODE Symbolic code of the information block
 * @property-read string|null $CODE Symbolic code of the information block (alias for IBLOCK_CODE)
 * @property-read string|null $IBLOCK_ID Information block identifier
 * @property-read string|null $NAME List name
 * @property-read string|null $DESCRIPTION List description
 * @property-read string|null $SORT Sorting order
 * @property-read string|null $ACTIVE Activity status (Y/N)
 * @property-read string|null $DATE_CREATE Creation date
 * @property-read string|null $CREATED_BY Creator identifier
 * @property-read string|null $TIMESTAMP_X Last modification timestamp
 * @property-read string|null $MODIFIED_BY Last modifier identifier
 * @property-read array|null $PICTURE List picture
 * @property-read string|null $LIST_PAGE_URL List page URL
 * @property-read string|null $CANONICAL_PAGE_URL Canonical page URL
 * @property-read string|null $SECTION_PAGE_URL Section page URL
 * @property-read string|null $DETAIL_PAGE_URL Detail page URL
 * @property-read string|null $SECTIONS_NAME Sections name label
 * @property-read string|null $SECTION_NAME Section name label
 * @property-read string|null $ELEMENTS_NAME Elements name label
 * @property-read string|null $ELEMENT_NAME Element name label
 * @property-read array|null $FIELDS List fields
 * @property-read array|null $SOCNET_GROUP_ID Social network group identifier
 * @property-read array|null $RIGHTS Access rights
 * @property-read string|null $BIZPROC Business process support flag (Y/N)
 */
class ListItemResult extends AbstractItem
{
    #[\Override]
    public function __get($offset)
    {
        return match ($offset) {
            'IBLOCK_CODE' => $this->data['CODE'] ?? null,
            default => parent::__get($offset),
        };
    }
}
