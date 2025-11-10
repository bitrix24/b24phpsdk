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

namespace Bitrix24\SDK\Services\Landing\Page\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read non-negative-int $ID
 * @property-read string $CODE
 * @property-read string $TITLE
 * @property-read string $DESCRIPTION
 * @property-read string $ACTIVE
 * @property-read non-negative-int $SITE_ID
 * @property-read string $CREATED_BY_ID
 * @property-read string $MODIFIED_BY_ID
 * @property-read string $DATE_CREATE
 * @property-read string $DATE_MODIFY
 * @property-read string $FOLDER
 * @property-read string $FOLDER_ID
 * @property-read string $SITEMAP
 * @property-read string $IN_SITEMAP
 * @property-read string $TPL_ID
 * @property-read string $TPL_CODE
 * @property-read string $PREVIEW_PICTURE
 * @property-read string $PREVIEW_TEXT
 * @property-read string $DETAIL_TEXT
 * @property-read string $DETAIL_PICTURE
 * @property-read string $META_TITLE
 * @property-read string $META_DESCRIPTION
 * @property-read string $META_KEYWORDS
 * @property-read string $META_ROBOTS
 * @property-read string $RULE
 * @property-read string $ADDITIONAL_FIELDS
 * @property-read string $XML_ID
 * @property-read array $LANDING_ID_INDEX
 * @property-read array $LANDING_ID_404
 * @property-read array $LANDING_ID_503
 * @property-read string $DOMAIN_ID
 * @property-read string $DOMAIN_NAME
 * @property-read string $DOMAIN_PROTOCOL
 * @property-read string $PUBLIC_URL
 * @property-read string $PREVIEW_URL
 * @property-read string $VIEWS
 * @property-read string $DATE_PUBLIC
 * @property-read string $PUBLICATION
 */
class PageItemResult extends AbstractItem
{
}
