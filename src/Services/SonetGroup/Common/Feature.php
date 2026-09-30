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

namespace Bitrix24\SDK\Services\SonetGroup\Common;

/**
 * Built-in workgroup features. Pass a string to featureAccess() for module-specific features.
 *
 * @see https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-feature-access.html
 */
enum Feature: string
{
    case photo = 'photo';
    case calendar = 'calendar';
    case tasks = 'tasks';
    case files = 'files';
    case blog = 'blog';
}
