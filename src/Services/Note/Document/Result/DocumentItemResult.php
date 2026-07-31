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

namespace Bitrix24\SDK\Services\Note\Document\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentSelectBuilder;
use Carbon\CarbonImmutable;

/**
 * A single knowledge-base document returned by `note.document.*`.
 *
 * @property-read int             $id
 * @property-read int             $collectionId
 * @property-read int|null        $parentId
 * @property-read string          $title
 * @property-read string|null     $markdown
 * @property-read int|null        $position
 * @property-read int|null        $createdBy
 * @property-read int|null        $updatedBy
 * @property-read CarbonImmutable $createdAt
 * @property-read CarbonImmutable $updatedAt
 */
#[OpenApiEntity(
    entityKey:     'bitrix.note.documentitemdto',
    selectBuilder: DocumentSelectBuilder::class,
)]
class DocumentItemResult extends AbstractAnnotatedItem
{
}
