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
 * @property-read string $ID Unique page template identifier
 * @property-read string $XML_ID Page template XML identifier
 * @property-read string $TYPE Page template type
 * @property-read string $TITLE Page template title
 * @property-read string $ACTIVE Activity status (Y/N)
 * @property-read bool $AVAILABLE Availability flag
 * @property-read array $SECTION Page template sections
 * @property-read string|null $DESCRIPTION Page template description
 * @property-read string $PREVIEW Preview image URL
 * @property-read string $PREVIEW2X Preview image URL (2x)
 * @property-read string $PREVIEW3X Preview image URL (3x)
 * @property-read string $APP_CODE Application code
 * @property-read int $REST REST API identifier
 * @property-read array $LANG Language settings
 * @property-read int $TIMESTAMP Page template timestamp
 * @property-read array $DESIGNED_BY Page template designer information
 * @property-read array $DATA Page template data
 */
class PageTemplateItemResult extends AbstractItem
{
}
