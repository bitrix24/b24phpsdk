<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Userfieldconfig\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read positive-int $id
 * @property-read positive-int $userFieldId
 * @property-read string $value
 * @property-read bool $def
 * @property-read int $sort
 * @property-read string|null $xmlId
 */
class UserfieldConfigEnumItemResult extends AbstractAnnotatedItem
{
}
