<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read bool $success
 * @property-read int  $chatId
 * @property-read int  $messageId
 * @property-read bool $existing
 */
class CreateChatItemResult extends AbstractAnnotatedItem
{
}
