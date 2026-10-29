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

namespace Bitrix24\SDK\Services\CRM\V3\Form\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read string|null $title
 * @property-read string|null $description
 * @property-read string|null $buttonCaption
 * @property-read string|null $buttonTextColor
 * @property-read string|null $buttonBackgroundColor
 * @property-read int|null    $backgroundImageId
 * @property-read bool|null   $copyrightRemoved
 */
class FormAppearanceItemResult extends AbstractAnnotatedItem
{
}
