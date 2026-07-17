<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read int                  $id
 * @property-read int|null             $mailboxId
 * @property-read string|null          $mailboxEmail
 * @property-read string               $subject
 * @property-read string               $from
 * @property-read string               $to
 * @property-read string|null          $cc
 * @property-read CarbonImmutable|null $date
 * @property-read bool|null            $isSeen
 * @property-read bool|null            $hasAttachments
 * @property-read string|null          $url
 * @property-read array|null           $bindings
 * @property-read string|null          $body
 */
class MessageItemResult extends AbstractAnnotatedItem
{
}
