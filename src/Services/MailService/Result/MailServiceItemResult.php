<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\MailService\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * Mail service item result.
 *
 * @property-read int $ID
 * @property-read string $SITE_ID
 * @property-read bool $ACTIVE
 * @property-read int $SORT
 * @property-read string $NAME
 * @property-read string $SERVER
 * @property-read int $PORT
 * @property-read bool $ENCRYPTION
 * @property-read string $LINK
 * @property-read string|null $ICON
 * @property-read string|null $SMTP_SERVER
 * @property-read int|null $SMTP_PORT
 * @property-read bool $SMTP_LOGIN_AS_IMAP
 * @property-read bool $SMTP_PASSWORD_AS_IMAP
 * @property-read bool|null $SMTP_ENCRYPTION
 * @property-read bool|null $UPLOAD_OUTGOING
 */
class MailServiceItemResult extends AbstractAnnotatedItem
{
}
