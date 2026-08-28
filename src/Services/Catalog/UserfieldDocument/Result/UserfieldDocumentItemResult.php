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

namespace Bitrix24\SDK\Services\Catalog\UserfieldDocument\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Dynamic userfield values (fieldN, where N is the portal-specific userfield ID) are also
 * accessible via the inherited magic __get(), but cannot be statically annotated here.
 *
 * @property-read int    $documentId
 * @property-read string $documentType
 */
class UserfieldDocumentItemResult extends AbstractItem
{
}
