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

namespace Bitrix24\SDK\Services\CRM\V3\FormEmbedding\Service;

/**
 * Ways to place a CRM form on a website.
 */
enum EmbeddingVariant: string
{
    // form embedded into the page
    case inline = 'inline';
    // form opened by a button or a link
    case click = 'click';
    // form shown automatically after a delay
    case auto = 'auto';
    // public link to the form page
    case link = 'link';
}
