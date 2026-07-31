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

namespace Bitrix24\SDK\Services\Mail;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Mail\MailboxField\Service\MailboxField;
use Bitrix24\SDK\Services\Mail\MessageField\Service\MessageField;
use Bitrix24\SDK\Services\Mail\RecipientField\Service\RecipientField;
use Bitrix24\SDK\Services\Mail\Service\Batch;
use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Services\Mail\Service\Message;
use Bitrix24\SDK\Services\Mail\Service\Recipient;

#[ApiServiceBuilderMetadata(new Scope(['mail']))]
class MailServiceBuilder extends AbstractServiceBuilder
{
    public function mailbox(): Mailbox
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Mailbox(
                new Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function mailboxField(): MailboxField
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new MailboxField($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function message(): Message
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Message(
                new Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function messageField(): MessageField
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new MessageField($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function recipient(): Recipient
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Recipient(
                new Batch($this->batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function recipientField(): RecipientField
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new RecipientField($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
}
