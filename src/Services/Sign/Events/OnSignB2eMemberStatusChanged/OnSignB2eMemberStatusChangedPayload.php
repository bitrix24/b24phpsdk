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

namespace Bitrix24\SDK\Services\Sign\Events\OnSignB2eMemberStatusChanged;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string $memberUid Unique member identifier
 * @property-read string $documentUid Unique document identifier
 * @property-read string|null $companyUid Unique company identifier (present when a company integration exists)
 * @property-read string $statusCode Member status code
 * @property-read string $statusName Member status name
 */
class OnSignB2eMemberStatusChangedPayload extends AbstractItem
{
}
