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
 * @property-read string $ID Unique template identifier
 * @property-read string $XML_ID Template XML identifier
 * @property-read string $TYPE Template type (PAGE, STORE)
 * @property-read string $TITLE Template title
 * @property-read string $ACTIVE Activity status (Y/N)
 * @property-read bool $AVAILABLE Availability flag
 * @property-read array $SECTION Template sections
 * @property-read string|null $DESCRIPTION Template description
 * @property-read string $PREVIEW Preview image URL
 * @property-read string $PREVIEW2X Preview image URL (2x)
 * @property-read string $PREVIEW3X Preview image URL (3x)
 * @property-read string $APP_CODE Application code
 * @property-read int $REST REST API identifier
 * @property-read array $LANG Language settings
 * @property-read int $TIMESTAMP Template timestamp
 * @property-read array $DESIGNED_BY Template designer information
 * @property-read array $DATA Template data
 */
class SiteTemplateItemResult extends AbstractItem
{
}
