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

namespace Bitrix24\SDK\Services\Landing\Demos\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $ID Record identifier
 * @property-read string $XML_ID Unique record code
 * @property-read string $APP_CODE Current application code
 * @property-read string $ACTIVE Activity status (Y/N)
 * @property-read string $TITLE Title
 * @property-read string $DESCRIPTION Description
 * @property-read string $PREVIEW_URL Preview URL
 * @property-read string $TYPE Type of the created site (STORE, PAGE)
 * @property-read string $TPL_TYPE Placed in the site/store (S) or page (P) creation wizard
 * @property-read array $MANIFEST Manifest
 * @property-read string $SHOW_IN_LIST Whether to show in the list of templates
 * @property-read string $PREVIEW Preview image URL
 * @property-read string $PREVIEW2X Preview image URL (2x)
 * @property-read string $PREVIEW3X Preview image URL (3x)
 * @property-read int $CREATED_BY_ID Identifier of the user who created the record
 * @property-read int $MODIFIED_BY_ID Identifier of the user who modified the record
 * @property-read string $DATE_CREATE Creation date
 * @property-read string $DATE_MODIFY Modification date
 */
class DemosItemResult extends AbstractItem
{
}