<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int         $id
 * @property-read string      $name
 * @property-read string      $email
 * @property-read string|null $senderName
 */
class MailboxItemResult extends AbstractAnnotatedItem
{
}
