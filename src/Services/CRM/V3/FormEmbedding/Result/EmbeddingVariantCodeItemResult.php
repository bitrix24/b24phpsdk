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
use Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service\EmbeddingVariant;

/**
 * One placement variant requested with a variant filter.
 *
 * @property-read EmbeddingVariant|null $variant
 * @property-read string|null           $code    embed code for inline, click and auto
 * @property-read string|null           $url     public form URL for the link variant
 */
class EmbeddingVariantCodeItemResult extends AbstractAnnotatedItem
{
}
