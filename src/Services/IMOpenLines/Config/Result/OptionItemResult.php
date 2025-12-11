<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMOpenLines\Config\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class OptionItemResult
 *
 * Represents a single open line configuration
 * 
 * @property-read int $ID Open line identifier
 * @property-read string $ACTIVE Active status (Y/N)
 * @property-read string $LINE_NAME Line name
 * @property-read string $CRM CRM integration enabled (Y/N)
 * @property-read string $CRM_CREATE CRM entity to create (lead/deal/contact/company)
 * @property-read string $CRM_CREATE_SECOND Secondary CRM entity
 * @property-read string $CRM_CREATE_THIRD Third CRM entity
 * @property-read string $CRM_FORWARD Forward to CRM (Y/N)
 * @property-read string $CRM_CHAT_TRACKER Chat tracker enabled (Y/N)
 * @property-read string $CRM_TRANSFER_CHANGE Transfer change enabled (Y/N)
 * @property-read string $CRM_SOURCE CRM source
 * @property-read int $QUEUE_TIME Queue time in seconds
 * @property-read int $NO_ANSWER_TIME No answer time in seconds
 * @property-read string $QUEUE_TYPE Queue distribution type (evenly/strictly/all)
 * @property-read string $CHECK_AVAILABLE Check operator availability (Y/N)
 * @property-read string $WATCH_TYPING Watch typing indicator (Y/N)
 * @property-read string $WELCOME_BOT_ENABLE Welcome bot enabled (Y/N)
 * @property-read string $WELCOME_MESSAGE Welcome message enabled (Y/N)
 * @property-read string $WELCOME_MESSAGE_TEXT Welcome message text
 * @property-read string $VOTE_MESSAGE Vote message enabled (Y/N)
 * @property-read int $VOTE_TIME_LIMIT Vote time limit
 * @property-read string $VOTE_BEFORE_FINISH Vote before finish (Y/N)
 * @property-read string $VOTE_CLOSING_DELAY Vote closing delay (Y/N)
 * @property-read string $VOTE_MESSAGE_1_TEXT Vote message 1 text
 * @property-read string $VOTE_MESSAGE_1_LIKE Vote message 1 like text
 * @property-read string $VOTE_MESSAGE_1_DISLIKE Vote message 1 dislike text
 * @property-read string $VOTE_MESSAGE_2_TEXT Vote message 2 text
 * @property-read string $VOTE_MESSAGE_2_LIKE Vote message 2 like text
 * @property-read string $VOTE_MESSAGE_2_DISLIKE Vote message 2 dislike text
 * @property-read string $AGREEMENT_MESSAGE Agreement message enabled (Y/N)
 * @property-read int $AGREEMENT_ID Agreement identifier
 * @property-read string $CATEGORY_ENABLE Category enabled (Y/N)
 * @property-read int $CATEGORY_ID Category identifier
 * @property-read string $WELCOME_BOT_JOIN Welcome bot join time (always/online/offline)
 * @property-read int $WELCOME_BOT_ID Welcome bot identifier
 * @property-read int $WELCOME_BOT_TIME Welcome bot time
 * @property-read string $WELCOME_BOT_LEFT Welcome bot left action
 * @property-read string $NO_ANSWER_RULE No answer rule (none/form/bot/text)
 * @property-read int $NO_ANSWER_FORM_ID No answer form identifier
 * @property-read int $NO_ANSWER_BOT_ID No answer bot identifier
 * @property-read string $NO_ANSWER_TEXT No answer text
 * @property-read string $WORKTIME_ENABLE Work time enabled (Y/N)
 * @property-read string $WORKTIME_FROM Work time from
 * @property-read string $WORKTIME_TO Work time to
 * @property-read string $WORKTIME_TIMEZONE Work time timezone
 * @property-read array $WORKTIME_HOLIDAYS Work time holidays
 * @property-read array $WORKTIME_DAYOFF Work time days off
 * @property-read string $WORKTIME_DAYOFF_RULE Work time day off rule
 * @property-read int $WORKTIME_DAYOFF_FORM_ID Work time day off form identifier
 * @property-read int $WORKTIME_DAYOFF_BOT_ID Work time day off bot identifier
 * @property-read string $WORKTIME_DAYOFF_TEXT Work time day off text
 * @property-read string $CLOSE_RULE Close rule (text/form/bot)
 * @property-read int $CLOSE_FORM_ID Close form identifier
 * @property-read int $CLOSE_BOT_ID Close bot identifier
 * @property-read string $CLOSE_TEXT Close text
 * @property-read int $FULL_CLOSE_TIME Full close time
 * @property-read string $AUTO_CLOSE_RULE Auto close rule (none/form/bot/text)
 * @property-read int $AUTO_CLOSE_FORM_ID Auto close form identifier
 * @property-read int $AUTO_CLOSE_BOT_ID Auto close bot identifier
 * @property-read int $AUTO_CLOSE_TIME Auto close time
 * @property-read string $AUTO_CLOSE_TEXT Auto close text
 * @property-read int $AUTO_EXPIRE_TIME Auto expire time
 * @property-read array $DATE_CREATE Date created
 * @property-read array $DATE_MODIFY Date modified
 * @property-read int $MODIFY_USER_ID Modifier user identifier
 * @property-read string $TEMPORARY Temporary status (Y/N)
 * @property-read string $XML_ID External identifier
 * @property-read string $LANGUAGE_ID Language identifier
 * @property-read int $QUICK_ANSWERS_IBLOCK_ID Quick answers infoblock identifier
 * @property-read int $SESSION_PRIORITY Session priority
 * @property-read string $TYPE_MAX_CHAT Type max chat
 * @property-read string $MAX_CHAT Max chat
 * @property-read string $OPERATOR_DATA Operator data (profile/queue)
 * @property-read string $DEFAULT_OPERATOR_DATA Default operator data
 * @property-read int $KPI_FIRST_ANSWER_TIME KPI first answer time
 * @property-read string $KPI_FIRST_ANSWER_ALERT KPI first answer alert (Y/N)
 * @property-read string $KPI_FIRST_ANSWER_LIST KPI first answer list
 * @property-read string $KPI_FIRST_ANSWER_TEXT KPI first answer text
 * @property-read int $KPI_FURTHER_ANSWER_TIME KPI further answer time
 * @property-read string $KPI_FURTHER_ANSWER_ALERT KPI further answer alert (Y/N)
 * @property-read string $KPI_FURTHER_ANSWER_LIST KPI further answer list
 * @property-read string $KPI_FURTHER_ANSWER_TEXT KPI further answer text
 * @property-read string $KPI_CHECK_OPERATOR_ACTIVITY KPI check operator activity (Y/N)
 * @property-read string $SEND_NOTIFICATION_EMPTY_QUEUE Send notification on empty queue (Y/N)
 * @property-read string $USE_WELCOME_FORM Use welcome form (Y/N)
 * @property-read int $WELCOME_FORM_ID Welcome form identifier
 * @property-read string $WELCOME_FORM_DELAY Welcome form delay (Y/N)
 * @property-read string $SEND_WELCOME_EACH_SESSION Send welcome each session (Y/N)
 * @property-read string $CONFIRM_CLOSE Confirm close (Y/N)
 * @property-read string $IGNORE_WELCOME_FORM_RESPONSIBLE Ignore welcome form responsible (Y/N)
 * @property-read array $QUEUE Queue of operator identifiers
 * @property-read array $QUEUE_FULL Full queue data with operator details
 * @property-read array $QUEUE_USERS_FIELDS Queue users additional fields
 * @property-read string $QUEUE_ONLINE Queue online status (Y/N)
 */
class OptionItemResult extends AbstractItem
{
}
