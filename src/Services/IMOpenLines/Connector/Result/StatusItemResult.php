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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class StatusItemResult
 *
 * Represents a single status item from imconnector.status method
 * 
 * @property-read string $LINE
 * @property-read string $CONNECTOR
 * @property-read bool   $CONFIGURED
 * @property-read bool   $STATUS
 */
class StatusItemResult extends AbstractItem
{
}
