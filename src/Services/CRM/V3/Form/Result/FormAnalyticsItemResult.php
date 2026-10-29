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
 * @property-read string|null $googleAnalyticsId
 * @property-read bool|null   $googleAnalyticsPageViewEnabled
 * @property-read string|null $yandexMetricaId
 */
class FormAnalyticsItemResult extends AbstractAnnotatedItem
{
}
