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

namespace Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read bool|null                       $formActive
 * @property-read bool|null                       $resourcesReady
 * @property-read string|null                     $publicUrl
 * @property-read EmbeddingVariantItemResult|null $inline
 * @property-read EmbeddingVariantItemResult|null $click
 * @property-read EmbeddingVariantItemResult|null $auto
 */
#[OpenApiEntity(entityKey: 'bitrix.crm.embeddingdto')]
class EmbeddingItemResult extends AbstractAnnotatedItem
{
}
