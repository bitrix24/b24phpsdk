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

namespace Bitrix24\SDK\Services\Sign\B2e\MySafeTail\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int|null $id Signed document identifier
 * @property-read string|null $title Document title
 * @property-read CarbonImmutable|null $create_date Document creation date
 * @property-read CarbonImmutable|null $signed_date Document signing date
 * @property-read int|null $creator_id User ID who created the document
 * @property-read int|null $member_id User ID of the signing party
 * @property-read string|null $role Employee role in the document: editor, reviewer, assignee, signer
 * @property-read string|null $file_url Download URL for the signed document
 */
class MySafeTailItemResult extends AbstractAnnotatedItem
{
}

