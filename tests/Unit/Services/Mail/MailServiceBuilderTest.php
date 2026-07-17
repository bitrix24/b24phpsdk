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

namespace Bitrix24\SDK\Tests\Unit\Services\Mail;

use Bitrix24\SDK\Services\Mail\MailServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(MailServiceBuilder::class)]
class MailServiceBuilderTest extends TestCase
{
    private MailServiceBuilder $serviceBuilder;

    public function testServicesAreCached(): void
    {
        $this->assertSame($this->serviceBuilder->mailbox(), $this->serviceBuilder->mailbox());
        $this->assertSame($this->serviceBuilder->mailboxField(), $this->serviceBuilder->mailboxField());
        $this->assertSame($this->serviceBuilder->message(), $this->serviceBuilder->message());
        $this->assertSame($this->serviceBuilder->messageField(), $this->serviceBuilder->messageField());
        $this->assertSame($this->serviceBuilder->recipient(), $this->serviceBuilder->recipient());
        $this->assertSame($this->serviceBuilder->recipientField(), $this->serviceBuilder->recipientField());
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->serviceBuilder = (new ServiceBuilder(
            new NullCore(),
            new NullBatch(),
            new NullBulkItemsReader(),
            new NullLogger()
        ))->getMailScope();
    }
}
