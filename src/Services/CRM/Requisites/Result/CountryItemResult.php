<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Requisites\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int $ID
 * @property-read string $CODE
 * @property-read string $TITLE
 */
class CountryItemResult extends AbstractCrmItem
{
}