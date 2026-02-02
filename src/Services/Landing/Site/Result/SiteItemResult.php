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

namespace Bitrix24\SDK\Services\Landing\Site\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $ID Site identifier
 * @property-read string|null $CODE Unique symbolic code of the site
 * @property-read bool $ACTIVE Site activity
 * @property-read string $TYPE Type of site (PAGE – regular site, STORE – store)
 * @property-read bool $DELETED Flag for deleted page
 * @property-read string $TITLE Title of the site
 * @property-read string|null $XML_ID External key for developer needs
 * @property-read string|null $DESCRIPTION Arbitrary description of the site
 * @property-read string|null $DOMAIN_ID Domain identifier
 * @property-read string|null $DOMAIN_NAME Domain of the site
 * @property-read int|null $LANDING_ID_INDEX ID of the page designated as the main page of the site
 * @property-read int|null $LANDING_ID_404 ID of the page designated as the site's 404 error page
 * @property-read int|null $TPL_ID Identifier of the view template
 * @property-read string|null $LANG Language identifier for the site
 * @property-read int $CREATED_BY_ID Identifier of the user who created it
 * @property-read int $MODIFIED_BY_ID Identifier of the user who modified it
 * @property-read CarbonImmutable $DATE_CREATE Creation date
 * @property-read CarbonImmutable $DATE_MODIFY Modification date
 * @property-read string|null $PUBLIC_URL Public URL of the site
 * @property-read string|null $PREVIEW_PICTURE Preview image of the site
 */
class SiteItemResult extends AbstractItem
{
}
