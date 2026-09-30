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
 * Built-in workgroup operations. Availability depends on the selected feature.
 *
 * @see https://apidocs.bitrix24.com/api-reference/sonet-group/sonet-group-feature-access.html
 */
enum FeatureOperation: string
{
    case view = 'view';
    case write = 'write';
    case viewAll = 'view_all';
    case sort = 'sort';
    case createTasks = 'create_tasks';
    case editTasks = 'edit_tasks';
    case deleteTasks = 'delete_tasks';
    case viewPost = 'view_post';
    case premoderatePost = 'premoderate_post';
    case writePost = 'write_post';
    case moderatePost = 'moderate_post';
    case fullPost = 'full_post';
    case viewComment = 'view_comment';
    case premoderateComment = 'premoderate_comment';
    case writeComment = 'write_comment';
    case moderateComment = 'moderate_comment';
    case fullComment = 'full_comment';
}
