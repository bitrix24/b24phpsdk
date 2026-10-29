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

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Settings keys differ per variant (click has a button block, auto has a delay); options list allowed values.
 *
 * @property-read string|null $code
 * @property-read string|null $previewUrl
 * @property-read array|null  $settings   null for the inline variant
 * @property-read array|null  $options    null for the inline variant
 */
class EmbeddingVariantItemResult extends AbstractAnnotatedItem
{
}
