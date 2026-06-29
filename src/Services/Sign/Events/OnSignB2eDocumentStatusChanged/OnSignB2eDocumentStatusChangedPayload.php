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

namespace Bitrix24\SDK\Services\Sign\Events\OnSignB2eDocumentStatusChanged;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string $documentUid Unique document identifier
 * @property-read string|null $companyUid Unique company identifier (present when a company integration exists)
 * @property-read string $statusCode Document status code
 * @property-read string $statusName Document status name
 */
class OnSignB2eDocumentStatusChangedPayload extends AbstractItem
{
}

