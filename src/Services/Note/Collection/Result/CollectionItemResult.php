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

namespace Bitrix24\SDK\Services\Note\Collection\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Note\Collection\Service\CollectionSelectBuilder;
use Carbon\CarbonImmutable;

/**
 * A single knowledge base ("collection") returned by `note.collection.*`.
 *
 * @property-read int             $id
 * @property-read string          $name
 * @property-read int|null        $position
 * @property-read string|null     $policyLevel
 * @property-read int|null        $createdBy
 * @property-read CarbonImmutable $createdAt
 * @property-read int|null        $updatedBy
 * @property-read CarbonImmutable $updatedAt
 */
#[OpenApiEntity(
    entityKey:     'bitrix.note.collectionitemdto',
    selectBuilder: CollectionSelectBuilder::class,
)]
class CollectionItemResult extends AbstractAnnotatedItem
{
}
