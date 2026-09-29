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

namespace Bitrix24\SDK\Services\IM\Chat;

enum ChatEntityType: string
{
    case VideoConf = 'VIDEOCONF';
    case AiAssistantPrivate = 'AI_ASSISTANT_PRIVATE';
    case Lines = 'LINES';
    case LiveChat = 'LIVECHAT';
    case Announcement = 'ANNOUNCEMENT';
    case Calendar = 'CALENDAR';
    case Mail = 'MAIL';
    case Crm = 'CRM';
    case SonetGroup = 'SONET_GROUP';
    case Tasks = 'TASKS';
    case TasksTask = 'TASKS_TASK';
    case Call = 'CALL';
}
