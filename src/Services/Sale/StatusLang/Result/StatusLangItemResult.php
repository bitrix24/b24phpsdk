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

namespace Bitrix24\SDK\Services\Sale\StatusLang\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class StatusLangItemResult - represents a single status language item
 *
 * @package Bitrix24\SDK\Services\Sale\StatusLang\Result
 *
 * @property-read string $statusId Status identifier
 * @property-read string $lid Language identifier
 * @property-read string $name Status name in this language
 * @property-read string $description Status description in this language
 */
class StatusLangItemResult extends AbstractItem
{
}
